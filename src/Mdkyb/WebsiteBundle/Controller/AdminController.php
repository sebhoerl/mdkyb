<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Yaml\Yaml;

/*
 * ACHTUNG!!!
 *
 * Diese Klasse ist bisher total hässlich und bekommt auf jeden Fall
 * nochmal ein Refactoring und vernünftige Kommentare.
 */

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $configuration = null;

    /**
     * @Route("/index", name="admin_index")
     */
    public function indexAction()
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:index.html.twig';

        $objects = $configuration['objects'];

        array_walk($objects, function(&$config, $name) {
            if (!isset($config['label'])) {
                $config['label'] = strtoupper($name);
            }
            if (!isset($config['description'])) {
                $config['description'] = '';
            }
        });

        return $this->render($template, array('objects' => $objects));
    }

    /**
     * @Route("/list/{name}", name="admin_list")
     * @Route("/list/{name}/page/{page}", name="admin_list_page")
     */
    public function listAction($name, $page = 1)
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:list.html.twig';

        $objectConfig = $configuration['objects'][$name];

        $fields = $objectConfig['fields'];
        array_walk($fields, function(&$config, $name) {
            if (!isset($config['label'])) {
                $config['label'] = strtoupper($name);
            }
            if (!isset($config['format'])) {
                $config['format'] = 'text';
            }
        });

        $order_by = $objectConfig['ordering']['field'];
        $order_dir = $objectConfig['ordering']['direction'];

        $request = $this->getRequest();
        $session = $request->getSession();

        if ($session->has('admin_ordering')) {
            $ordering = $session->get('admin_ordering');
            if ($ordering['name'] == $name) {
                $order_by = $ordering['field'];
                $order_dir = $ordering['direction'];
            }
        }

        if ($request->request->has('order_by') && $request->request->has('order_dir')) {
            $order_by = $request->request->get('order_by');
            $order_dir = $request->request->get('order_dir');
        }

        if ($order_by === null || !isset($fields[$order_by]) || $fields[$order_by]['orderable'] == false) {
            foreach (array_keys($fields) as $name => $config) {
                if ($config['orderable']) {
                    $order_by = $name;
                    break;
                }
            }
        }

        if ($order_dir != 'asc' && $order_dir != 'desc') {
            $order_dir = 'asc';
        }

        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('o')
            ->from($objectConfig['entity'], 'o');

        if ($order_by !== null) {
            $builder->orderBy('o.' . $order_by, $order_dir);
        }

        $session->set('ordering', array(
            'field' => $order_by,
            'direction' => $order_dir,
            'name' => $name,
        ));

        $filter = null;

        if ($session->has('filter')) {
            $session_filter = $session->get('filter');
            if ($session_filter['name'] == $name) {
                $filter = $session_filter;
            }
        }

        if ($request->request->has('filter_field') && $request->request->has('filter_value')) {
            $filter = array(
                'field' => $request->request->get('filter_field'),
                'value' => $request->request->get('filter_value'),
            );
        }

        if (
            $filter !== null && 
            isset($filter['field']) &&
            isset($filter['value']) &&
            isset($fields[$filter['field']]) &&
            $fields[$filter['field']]['filterable']
        ) {
            $builder->where($builder->expr()->like('o.' . $filter['field'], '?1'));
        } else {
            $filter = null;
        }

        $query = $builder->getQuery();

        $builder->select('count(o)');
        $countQuery = $builder->getQuery();

        if ($filter !== null) {
            $query->setParameter(1, '%' . $filter['value'] . '%');
            $countQuery->setParameter(1, '%' . $filter['value'] . '%');
        } else {
            $filter = array('field' => null, 'value' => '');
        }

        $count = $countQuery->getSingleScalarResult();
        $PER_PAGE = 10;

        $max_page = ceil($count / $PER_PAGE);
        $page = max(1, $page);
        $page = min($max_page, $page);

        $query->setMaxResults($PER_PAGE);
        $query->setFirstResult(($page - 1) * $PER_PAGE);

        $objects = $query->getResult();

        $pages = array();
        $pages[] = 1;
        if (2 < $max_page) $pages[] = 2;
        $pages[] = $max_page;
        if ($max_page - 1 > 1) $pages[] = $max_page - 1;
        $pages[] = $page;
        if ($page - 1 > 1) $pages[] = $page - 1;
        if ($page + 1 < $max_page) $pages[] = $page + 1;
        $pages = array_unique($pages);
        sort($pages);

        $display_pages = array();

        if ($max_page > 1) {

            $pagecount = count($pages);

            for ($i = 0; $i < $pagecount; $i++) {
                if ($pages[$i] == $page) {
                    $display_pages[] = array('number' => $pages[$i], 'type' => 'current');
                } else {
                    $display_pages[] = array('number' => $pages[$i], 'type' => 'link');
                }
                if (isset($pages[$i+1])) {
                    if ($pages[$i+1] != $pages[$i] + 1) {
                        $display_pages[] = array('type' => 'separator');
                    }
                }
            }

        } else {
            $display_pages[] = array('number' => 1, 'type' => 'current');
        }

        $pages = array(
            'next' => $page != $max_page ? $page + 1 : 0,
            'prev' => $page != 1 ? $page - 1 : 0,
            'first' => 1,
            'last' => $max_page,
        );

        return $this->render($template, array(
            'objects' => $objects, 
            'fields' => $fields,
            'object_name' => $name,
            'identifier' => $objectConfig['identifier'],
            'order_by' => $order_by,
            'order_dir' => $order_dir,
            'filter_field' => $filter['field'],
            'filter_value' => $filter['value'],
            'pages' => $display_pages
        ));
    }

    /**
     * @Route("/edit/{name}/{id}", name="admin_edit")
     */
    public function editAction($name, $id)
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:edit.html.twig';

        $objectConfig = $configuration['objects'][$name];

        $object = $this->getEntityManager()->getRepository($objectConfig['entity'])->find($id);
        $form = $this->buildForm($object, $objectConfig['fields']);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($object);
                $em->flush();

                $request->getSession()->setFlash('admin.edited', true);

                return $this->redirect($this->generateUrl('admin_edit', array(
                    'name' => $name,
                    'id' => $id,
                )));
            }
        }

        return $this->render($template, array(
            'form' => $form->createView(),
            'object' => $objectConfig,
            'object_id' => $id,
            'object_name' => $name,
        ));
    }

    /**
     * @Route("/delete/{name}/{id}", name="admin_delete")
     */
    public function deleteAction($name, $id)
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:delete.html.twig';

        $objectConfig = $configuration['objects'][$name];
        $object = $this->getEntityManager()->getRepository($objectConfig['entity'])->find($id);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $em = $this->getEntityManager();
            $em->remove($object);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_list', array(
                'name' => $name,
            )));
        }

        return $this->render($template, array(
            'object' => $objectConfig,
        ));
    }

    /**
     * @Route("/create/{name}", name="admin_create")
     */
    public function createAction($name)
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:create.html.twig';

        $objectConfig = $configuration['objects'][$name];

        $object = new $objectConfig['entity'];
        $form = $this->buildForm($object, $objectConfig['fields']);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($object);
                $em->flush();

                $request->getSession()->setFlash('admin.created', true);

                $getter = 'get' . $objectConfig['identifier'];
                $id = $object->{$getter}();

                return $this->redirect($this->generateUrl('admin_edit', array(
                    'name' => $name,
                    'id' => $id,
                )));
            }
        }

        return $this->render($template, array(
            'form' => $form->createView(),
            'object_name' => $name,
        ));
    }

    protected function buildForm($object, $fieldConfig)
    {
        $builder = $this->createFormBuilder($object);

        foreach ($fieldConfig as $name => $config) {
            if ($config['edit']) {
                $builder->add($name, $config['type'], array(
                    'label' => $config['label']
                ));
            }
        }

        return $builder->getForm();
    }

    protected function getTemplate($name)
    {
        $configuration = $this->getConfiguration();
        return $configuration['templates'][$name];
    }

    protected function getConfiguration()
    {
        if (isset($this->configuration)) {
            return $this->configuration;
        }

        $path = __DIR__ . '/../Resources/config/admin.yml';

        $treeBuilder = new TreeBuilder();
        $admin = $treeBuilder->root('admin');

        $admin
            ->children()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('index')
                            ->defaultValue('MdkybWebsiteBundle:Admin:index.html.twig')
                        ->end()
                        ->scalarNode('list')
                            ->defaultValue('MdkybWebsiteBundle:Admin:list.html.twig')
                        ->end()
                        ->scalarNode('edit')
                            ->defaultValue('MdkybWebsiteBundle:Admin:edit.html.twig')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('objects')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('description')
                                ->defaultValue('')
                            ->end()
                            ->scalarNode('entity')->end()
                            ->scalarNode('identifier')
                                ->defaultValue('id')
                            ->end()
                            ->arrayNode('ordering')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('field')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('direction')
                                        ->defaultValue('asc')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fields')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('label')
                                            ->defaultNull()
                                        ->end()
                                        ->booleanNode('list')
                                            ->defaultTrue()
                                        ->end()
                                        ->booleanNode('show')
                                            ->defaultTrue()
                                        ->end()
                                        ->booleanNode('edit')
                                            ->defaultTrue()
                                        ->end()
                                        ->scalarNode('format')
                                            ->defaultValue('text')
                                        ->end()
                                        ->scalarNode('type')
                                            ->defaultNull()
                                        ->end()
                                        ->booleanNode('orderable')
                                            ->defaultTrue()
                                        ->end()
                                        ->booleanNode('filterable')
                                            ->defaultTrue()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $tree = $treeBuilder->buildTree();

        $config = Yaml::parse(file_get_contents($path));
        $config = $tree->normalize($config);
        $config = $tree->finalize($config);
        
        //echo "<pre>";
        //print_r($config);
        //echo "</pre>";

        $this->configuration = $config;
        return $config;
    }
}

<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Response;
use Swift_Message;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Mdkyb\WebsiteBundle\Entity\Email;
use Mdkyb\WebsiteBundle\Entity\Member;

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

    protected function processDownload($download)
    {
        $download->upload();
    }

    protected function processImage($download)
    {
        $download->upload();
    }

    /**
     * @Route("/email", name="admin_email")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function emailAction()
    {
        $count = $this->getEntityManager()
            ->createQuery('select count(e) from MdkybWebsiteBundle:Email e')
            ->getSingleScalarResult();

        if ($count > 0) {
            $this->getRequest()->getSession()->set('email_queue', $count);

            return $this->redirect($this->generateUrl(
                'admin_email_spool'
            ));
        }

        $email = new Email();
        $form = $this->createFormBuilder($email)
            ->add('title')
            ->add('content', 'textarea')
            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $members = $em->getRepository('MdkybWebsiteBundle:Member')->findAll();

                foreach ($members as $member) {
                    $new = clone $email;
                    $new->setMember($member);
                    $em->persist($new);
                }

                $em->flush();

                return $this->redirect($this->generateUrl(
                    'admin_email'
                ));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/email/spool", name="admin_email_spool")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function spoolEmailAction()
    {
        $count = $this->getEntityManager()
            ->createQuery('select count(e) from MdkybWebsiteBundle:Email e')
            ->getSingleScalarResult();

        $email = null;
        if ($count > 0) {
            $email = $this->getEntityManager()
                ->createQuery('select e, m from MdkybWebsiteBundle:Email e join e.member m')
                ->setMaxResults(1)
                ->getSingleResult();

            try {
                $message = Swift_Message::newInstance()
                    ->setSubject($email->getTitle())
                    ->setFrom('no-reply@magdeburgerkybernetiker.de')
                    ->setTo(array($email->getMember()->getEmail() => $email->getMember()->getName()))
                    ->setBody($email->getContent())
                ;

                $this->get('mailer')->send($message);
            } catch (\Exception $e) {}

            $em = $this->getEntityManager();
            $em->remove($email);
            $em->flush();
        }

        $queue = $this->getRequest()->getSession()->get('email_queue');

        return array('count' => $count, 'email' => $email, 'queue' => $queue);
    }

    public function registerAction($object, $objectName, $objectInfo)
    {
        $request = $this->getRequest();
        $request->getSession()->setFlash('admin.registration', true);

        $object->generateKey();
        $em = $this->getEntityManager();
        $em->persist($object);
        $em->flush();

        $mailer = $this->get('mailer');

        $message = Swift_Message::newInstance()
            ->setSubject('Dein Account im Verein Magdeburger Kybernetiker')
            ->setFrom('no-reply@magdeburgerkybernetiker.de')
            ->setTo(array($object->getEmail() => $object->getName()))
            ->setBody($this->renderView('MdkybWebsiteBundle:Admin:reg_mail.html.twig', array('member' => $object)))
        ;

        $mailer->send($message);

        return $this->redirect($this->generateUrl(
            'admin_edit', array('name' => $objectName, 'id' => $object->getId())
        ));
    }

    public function acceptMemberAction($object, $objectName, $objectInfo)
    {
        $em = $this->getEntityManager();

        $member = new Member();
        $member->setEmail($object->getEmail());
        $member->setName(
            sprintf('%s %s %s',
                $object->getTitle(),
                $object->getSurename(),
                $object->getLastname()
            )
        );
        $member->setFunction(0);
        $member->setPassword('');
        $member->setPaid(false);
        $member->setRoles(array('ROLE_MEMBER'));

        $em->persist($member);
        $em->remove($object);

        $em->flush();

        return $this->registerAction($member, 'member', array());
    }

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

        if (null !== ($role = $objectConfig['secure']) && !$this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

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
            foreach ($fields as $fname => $config) {
                if ($config['orderable']) {
                    $order_by = $fname;
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

        $max_page = max(1, ceil($count / $PER_PAGE));
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
            'pages' => $display_pages,
            'config' => $objectConfig
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

        if (null !== ($role = $objectConfig['secure']) && !$this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $object = $this->getEntityManager()->getRepository($objectConfig['entity'])->find($id);
        $form = $this->buildForm($object, $objectConfig['fields']);

        $original = clone $object;

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();

                if ($handler = $objectConfig['save_handler']) {
                    $this->{$handler}($object);
                }

                $fields = $objectConfig['fields'];
                foreach ($fields as $fname => $field) {
                    if ($field['type'] == 'password') {
                        $getter = 'get' . $fname;
                        $setter = 'set' . $fname;
                        $new = $object->{$getter}();
                        $old = $original->{$getter}();

                        if ($new !== null) {
                            $encFactory = $this->get('security.encoder_factory');
                            $encoder = $encFactory->getEncoder($object);

                            $saltGetter = 'get' . $field['salt'];
                            $salt = $object->{$saltGetter}();

                            $new = $encoder->encodePassword($new, $salt);
                            $object->{$setter}($new);
                        } else {
                            $object->{$setter}($old);
                        }
                    }
                }

                $em->persist($object);
                $em->flush();

                $request->getSession()->setFlash('admin.edited', true);

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
            'object' => $objectConfig,
            'object_id' => $id,
            'object_name' => $name,
            'config' => $objectConfig,
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

        if (null !== ($role = $objectConfig['secure']) && !$this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $object = $this->getEntityManager()->getRepository($objectConfig['entity'])->find($id);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $em = $this->getEntityManager();
            $em->remove($object);
            $em->flush();

            $request->getSession()->setFlash('admin.deleted', true);

            return $this->redirect($this->generateUrl('admin_list', array(
                'name' => $name,
            )));
        }

        return $this->render($template, array(
            'object' => $object,
            'object_name' => $name,
            'config' => $objectConfig,
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

        if (null !== ($role = $objectConfig['secure']) && !$this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $object = new $objectConfig['entity'];
        $form = $this->buildForm($object, $objectConfig['fields']);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();

                if ($handler = $objectConfig['save_handler']) {
                    $this->{$handler}($object);
                }

                $fields = $objectConfig['fields'];
                foreach ($fields as $fname => $field) {
                    if ($field['type'] == 'password') {
                        $getter = 'get' . $fname;
                        $setter = 'set' . $fname;
                        $new = $object->{$getter}();

                        $encFactory = $this->get('security.encoder_factory');
                        $encoder = $encFactory->getEncoder($object);

                        $saltGetter = 'get' . $field['salt'];
                        $salt = $object->{$saltGetter}();

                        $new = $encoder->encodePassword($new, $salt);
                        $object->{$setter}($new);
                    }
                }

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
            'config' => $objectConfig,
        ));
    }

    /**
     * @Route("/action/{action}/{name}/{id}", name="admin_action")
     */
    public function actionAction($action, $name, $id)
    {
        $configuration = $this->getConfiguration();
        $template = 'MdkybWebsiteBundle:Admin:delete.html.twig';

        $objectConfig = $configuration['objects'][$name];

        if (null !== ($role = $objectConfig['secure']) && !$this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
        
        $object = $this->getEntityManager()->getRepository($objectConfig['entity'])->find($id);

        foreach ($objectConfig['actions'] as $aname => $actionConfig) {
            if ($aname == $action) {
                $callback = $actionConfig['controller'];
                return $this->{$callback}($object, $name, $objectConfig);
            }
        }

        return new Response("NULL");
    }

    protected function buildForm($object, $fieldConfig)
    {
        $builder = $this->createFormBuilder($object);

        foreach ($fieldConfig as $name => $config) {
            if ($config['edit']) {
                /*$builder->add($name, $config['type'], array(
                    'label' => $config['label']
                ));*/
                //$builder->add($name, 'choice');
                $builder->add($name, $config['type'], array_merge(
                    array('label' => $config['label']),
                    $config['options']
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
                            ->scalarNode('save_handler')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('secure')
                                ->defaultNull()
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
                            ->arrayNode('actions')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('controller')->end()
                                        ->scalarNode('label')->end()
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
                                        ->scalarNode('salt')
                                            ->defaultValue('salt')
                                        ->end()
                                        ->booleanNode('orderable')
                                            ->defaultTrue()
                                        ->end()
                                        ->booleanNode('filterable')
                                            ->defaultTrue()
                                        ->end()
                                        ->arrayNode('options')
                                            ->useAttributeAsKey('name')
                                            ->prototype('variable')->end()
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

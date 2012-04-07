<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class StaticContentController extends AbstractController
{
    /**
     * @Route("/static/{slug}", name="static")
     * @Template()
     */
    public function showAction($slug)
    {
        $page = $this->getEntityManager()
            ->getRepository('MdkybWebsiteBundle:StaticContent')
            ->findOneBy(array('name' => $slug));

        if ($page === null)
        {
            throw $this->createNotFoundException('Die Seite existiert nicht!');
        }

        return array('page' => $page);
    }

    /**
     * @Template()
     */
    public function showMenuAction()
    {
        $menu = $this->getEntityManager()
            ->getRepository('MdkybWebsiteBundle:StaticContent')
            ->findAll();

        return array('menu' => $menu);
    }
}

<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the static pages
 */
class StaticContentController extends AbstractController
{
    /**
     * Shows a static page
     * 
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
     * Shows the menu for the static pages
     * 
     * @Template()
     */
    public function showMenuAction()
    {
        $menu = $this->getEntityManager()
            ->getRepository('MdkybWebsiteBundle:StaticContent')
            ->findAll();

        return array('menu' => $menu);
    }

    /**
     * Shows the links for the internal area
     * 
     * @Route("/intern", name="internal")
     * @Template()
     */
    public function showInternalAreaAction()
    {
        return array();
    }

    /**
     * Shows the statute
     * 
     * @Route("/satzung", name="statute")
     * @Template()
     */
    public function showStatuteAction()
    {
        return array();
    }
}

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
     * Shows the menu for the static pages
     * 
     * @Template()
     */
    public function showMenuAction()
    {
        return array();
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
     * Shows the links for the submenu
     * 
     * @Route("/verein", name="submenu")
     * @Template()
     */
    public function showSubMenuAction()
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

    /**
     * Shows legal notice
     * 
     * @Route("/rechtliches", name="legal")
     * @Template()
     */
    public function showLegalNoticeAction()
    {
        return array();
    }

    /**
     * Shows owner
     * 
     * @Route("/impressum", name="owner")
     * @Template()
     */
    public function showOwnerAction()
    {
        return array();
    }
}

<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Abstract controller that provides some common methods
 */
abstract class AbstractController extends Controller
{
    /**
     * Returns the global entity manager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }
}

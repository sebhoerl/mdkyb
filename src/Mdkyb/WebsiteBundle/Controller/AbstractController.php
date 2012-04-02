<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }
}

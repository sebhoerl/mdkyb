<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Datetime;

/**
 * Handles gallery requests
 */
class GalleryController extends AbstractController
{
    /**
     * Previews all galleries
     * 
     * @Route("/gallery", name="gallery")
     * @Template()
     */
    public function showAllAction()
    {
        $galleries = $this->getEntityManager()
            ->createQuery('select g, i from MdkybWebsiteBundle:Gallery g join g.images i order by g.id desc')
            ->getResult();

        return array('galleries' => $galleries);
    }

    /**
     * Shows a gallery
     * 
     * @Route("/gallery/{id}", name="show_gallery")
     * @Template()
     */
    public function showGalleryAction($id)
    {
        $gallery = $this->getEntityManager()
            ->createQuery('select g, i from MdkybWebsiteBundle:Gallery g join g.images i where g.id = :id order by g.id desc')
            ->setParameter('id', $id)
            ->getSingleResult();

        return array('gallery' => $gallery);
    }
}

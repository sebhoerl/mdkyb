<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Datetime;

class GalleryController extends AbstractController
{
    /**
     * @Route("/gallery", name="gallery")
     * @Template()
     */
    public function showAllAction()
    {
        $galleries = $this->getEntityManager()
            ->createQuery('select g, i from MdkybWebsiteBundle:Gallery g join g.images i')
            ->getResult();

        return array('galleries' => $galleries);
    }
}

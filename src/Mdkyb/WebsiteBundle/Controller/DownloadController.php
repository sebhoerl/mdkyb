<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Datetime;

class DownloadController extends AbstractController
{
    /**
     * @Route("/downloads", name="downloads")
     * @Template()
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function listAction()
    {
        $downloads = $this->getEntityManager()
            ->createQuery('select j from MdkybWebsiteBundle:Download j')
            ->getResult();

        return array('downloads' => $downloads);
    }

    /**
     * @Route("/download/{id}", name="download")
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function showAction($id)
    {
        $download = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Download')->find($id);
        if ($download == null) {
            throw $this->createNotFoundException('Die Datei existiert nicht!');
        }

        return new Response(file_get_contents($download->getPath()), 200, array(
            'Content-Type' => $download->getMimeType(),
            'Content-Disposition' => 'attachment; filename=' . $download->getOriginalFileName(),
        ));
    }
}

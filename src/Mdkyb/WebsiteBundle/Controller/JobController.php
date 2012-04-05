<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Datetime;

class JobController extends AbstractController
{
    const POSTS_PER_PAGE = 10;

    /**
     * @Route("/jobs", name="jobs")
     * @Template()
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function listAction()
    {
        $jobs = $this->getEntityManager()
            ->createQuery('select j from MdkybWebsiteBundle:Job j where j.expiresAt > ?1')
            ->setParameter(1, new Datetime('now'))
            ->getResult();

        return array('jobs' => $jobs);
    }

    /**
     * @Route("/job/{id}", name="job_show")
     * @Template()
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function showAction($id)
    {
        $job = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Job')->find($id);
        if ($job == null) {
            throw $this->createNotFoundException('Die Seite existiert nicht!');
        }

        return array('job' => $job);
    }
}

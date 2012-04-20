<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Datetime;

/**
 * Handles the job section
 */
class JobController extends AbstractController
{
    const POSTS_PER_PAGE = 10;

    /**
     * Lists all jobs
     * 
     * @Route("/jobs", name="jobs")
     * @Template()
     * @Secure(roles="ROLE_MEMBER")
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
     * Shows a certain job offer
     * 
     * @Route("/job/{id}", name="job_show")
     * @Template()
     * @Secure(roles="ROLE_MEMBER")
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

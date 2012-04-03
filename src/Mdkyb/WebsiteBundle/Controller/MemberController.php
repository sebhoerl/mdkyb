<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use JMS\SecurityExtraBundle\Annotation\Secure;

class MemberController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return array();
    }

    /**
     * @Route("/profile", name="profile")
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     * @Template()
     */
    public function profileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        /* TODO: Implement form as soon as there are additional information
         in the Member class. */

        return array('user' => $user);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {}

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {}
}

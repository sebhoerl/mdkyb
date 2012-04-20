<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Mdkyb\WebsiteBundle\Model\Registration;

use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Handles member section requests
 */
class MemberController extends AbstractController
{
    /**
     * Displays the login page
     * 
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return array();
    }

    /**
     * Displays a user's profile
     * 
     * @Route("/profile", name="profile")
     * @Secure(roles="ROLE_MEMBER")
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
     * Dummy route for login_check
     * 
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {}

    /**
     * Dummy route for logout
     * 
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {}

    /**
     * Displays a messages that the registration has been completed
     * 
     * @Route("/registration_complete", name="registration_complete")
     * @Template()
     */
    public function registrationCompleteAction()
    {
        return array();
    }

    /**
     * Displays a form to set a new password if the user has a valid
     * registration key.
     * 
     * @Route("/register/{id}/{key}", name="register")
     * @Template()
     */
    public function registerAction($id, $key)
    {
        if (strlen($key) == 32)
        {
            $member = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Member')->find($id);
            if ($member === null) {
                throw $this->createNotFoundException('Der Benutzer existiert nicht!');
            }

            if ($member->getRegistrationKey() === $key) {
                $registration = new Registration();

                $form = $this->createFormBuilder($registration)
                    ->add('password', 'repeated', array(
                        'type' => 'password',
                    ))
                    ->getForm();

                $request = $this->getRequest();
                if ($request->getMethod() == 'POST') {
                    $form->bindRequest($request);

                    if ($form->isValid()) {
                        $em = $this->getEntityManager();

                        $encFactory = $this->get('security.encoder_factory');
                        $encoder = $encFactory->getEncoder($member);
                        $password = $encoder->encodePassword($registration->getPassword(), $member->getSalt());
                        
                        $member->setPassword($password);
                        $member->setRegistrationKey('');

                        $em->persist($member);
                        $em->flush();

                        return $this->redirect($this->generateUrl(
                            'registration_complete'
                        ));
                    }
                }

                return array('member' => $member, 'form' => $form->createView());
            }
        }

        throw $this->createNotFoundException('Der Key ist abgelaufen!');
    }
}

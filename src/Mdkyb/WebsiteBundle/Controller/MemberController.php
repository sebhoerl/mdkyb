<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Mdkyb\WebsiteBundle\Model\Registration;

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

    /**
     * @Route("/registration_complete", name="registration_complete")
     * @Template()
     */
    public function registrationCompleteAction()
    {
        return array();
    }

    /**
     * @Route("/register/{email}/{key}", name="register")
     * @Template()
     */
    public function registerAction($email, $key)
    {
        if (strlen($key) == 32)
        {
            $member = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Member')->find($email);
            if ($member === null) {
                throw $this->createNotFoundException('Ein Benutzer mit dieser Adresse existiert nicht!');
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

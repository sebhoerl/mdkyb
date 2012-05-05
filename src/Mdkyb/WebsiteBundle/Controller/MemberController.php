<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Mdkyb\WebsiteBundle\Model\Registration;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Mdkyb\WebsiteBundle\Form\ChangeProfileType;
use Mdkyb\WebsiteBundle\Form\ChangePasswordType;
use Mdkyb\WebsiteBundle\Model\ChangePasswordModel;

use Symfony\Component\Form\FormError;
use Swift_Message;

use Datetime;

use Mdkyb\WebsiteBundle\Entity\Image;
use Mdkyb\WebsiteBundle\Entity\MembershipApplication;

use Mdkyb\WebsiteBundle\Form\MembershipApplicationType;

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
        $user = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Member')->find($user->getId());

        $request = $this->getRequest();
        $em = $this->getEntityManager();

        $newImage = new Image();
        $imageExists = false;

        if ($user->getImage() === null) {
            $user->setImage($newImage);
        } else {
            $imageExists = true;
        }

        $passwordModel = new ChangePasswordModel();
        $passwordForm = $this->createForm(new ChangePasswordType(), $passwordModel);

        $profileForm = $this->createForm(new ChangeProfileType(), $user);

        if ('POST' === $request->getMethod()) {
            if ($request->request->has('change_password')) {
                $passwordForm->bindRequest($request);

                if ($passwordForm->isValid()) {
                    $encFactory = $this->get('security.encoder_factory');
                    $encoder = $encFactory->getEncoder($user);
                    $password = $encoder->encodePassword($passwordModel->getOldPassword(), $user->getSalt());

                    if ($password === $user->getPassword()) {
                        $newPassword = $encoder->encodePassword($passwordModel->getNewPassword(), $user->getSalt());
                        $user->setPassword($newPassword);
                        $em->persist($user);
                        $em->flush();

                        $request->getSession()->setFlash('profile.password_changed', true);
                        return $this->redirect($this->generateUrl('profile'));
                    } else {
                        $passwordForm['oldPassword']->addError(new FormError(
                            'Das alte Passwort war falsch!'
                        ));
                    }
                }
            }

            if ($request->request->has('change_profile')) {
                $profileForm->bindRequest($request);

                if ($profileForm->isValid()) {
                    $image = $user->getImage();
                    $image->setTitle($user->getName());

                    if ($image->file !== null) {
                        $image->upload();
                        $em->persist($image);
                    } elseif(!$imageExists) {
                        $user->setImage(null);
                    }

                    $em->persist($user);
                    $em->flush();

                    $request->getSession()->setFlash('profile.profile_changed', true);
                    return $this->redirect($this->generateUrl('profile'));
                }
            }
        }

        return array(
            'user' => $user, 
            'password_form' => $passwordForm->createView(), 
            'profile_form' => $profileForm->createView()
        );
    }

    protected function createRecoveryHash($user, $offset = 0) {
        $id = $user->getId();
        $password = $user->getPassword();

        $now = time();
        $hour = $now - $now % 3600 - $offset * 3600;

        return md5('47&4$' . $password . $id . $hour);
    }

    protected function checkRecoveryHash($user, $hash) {
        for ($i = 0; $i < 12; $i++) {
            if ($this->createRecoveryHash($user, $i) === $hash) {
                return true;
            }
        }

        return false;
    }

    /**
     * Displays a form that recovers the password
     * 
     * @Route("/recover-password/{id}/{hash}", name="recover_password", defaults={"id"=0, "hash"=""})
     * @Template()
     */
    public function recoverPasswordAction($id = 0, $hash = '')
    {
        $request = $this->getRequest();
        $em = $this->getEntityManager();

        if ($id == 0) {
            $form = $this->createFormBuilder()
                ->add('email', 'email', array('label' => 'E-Mail Addresse:'))
                ->getForm();

            if ('POST' === $request->getMethod()) {
                $form->bindRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    if (null !== $user = $em->getRepository('MdkybWebsiteBundle:Member')->findOneByEmail($data['email'])) {

                        $hash = $this->createRecoveryHash($user);

                        $message = Swift_Message::newInstance()
                            ->setSubject('Dein Passwort beim Verein Magdeburger Kybernetiker')
                            ->setFrom('no-reply@magdeburgerkybernetiker.de')
                            ->setTo(array($user->getEmail() => $user->getName()))
                            ->setBody($this->renderView('MdkybWebsiteBundle:Member:recover_mail.html.twig', 
                                array('user' => $user, 'hash' => $hash)
                            ))
                        ;

                        $this->get('mailer')->send($message);

                        $request->getSession()->setFlash('password_recovery', 'ok');
                    } else {
                        $request->getSession()->setFlash('password_recovery', 'error');
                    }
                }

                return $this->redirect($this->generateUrl('recover_password'));
            }

            return array('state' => 'email', 'form' => $form->createView());
        } else {
            $user = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Member')->find($id);
            if ($user === null) {
                throw $this->createNotFoundException('Der Benutzer existiert nicht!');
            }

            if (!$this->checkRecoveryHash($user, $hash)) {
                throw $this->createNotFoundException('Der Hash ist ungültig!');
            }

            $form = $this->createFormBuilder()
                ->add('password', 'repeated', array(
                    'type' => 'password',
                    'options' => array('label' => 'Neues Passwort:'),
                    'invalid_message' => 'Die Passwörter müssen übereinstimmen!'
                ))
                ->getForm();

            $request = $this->getRequest();
            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                
                if ($form->isValid()) {
                    $data = $form->getData();

                    $encFactory = $this->get('security.encoder_factory');
                    $encoder = $encFactory->getEncoder($user);
                    $password = $encoder->encodePassword($data['password'], $user->getSalt());

                    $user->setPassword($password);

                    $em->persist($user);
                    $em->flush();

                    $request->getSession()->setFlash('password_recovered', true);
                    return $this->redirect($this->generateUrl('login'));
                }
            }

            return array('state' => 'password', 'form' => $form->createView(), 'user' => $user);
        }
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
                        'options' => array('label' => 'Passwort:'),
                        'invalid_message' => 'Die Passwörter müssen übereinstimmen!'
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

    /**
     * Displays the member board
     * 
     * @Route("/vorstand", name="board")
     * @Template()
     */
    public function showBoardAction()
    {
        $users = $this->getEntityManager()
            ->createQuery('select u from MdkybWebsiteBundle:Member u where u.function > 0 order by u.function asc')
            ->getResult();

        return array('users' => $users);
    }

    const MEMBERS_PER_PAGE = 20;

    /**
     * Shows all members
     * 
     * @Route("/mitglieder/{page}", name="members")
     * @Template()
     */
    public function showMembersAction($page = 1)
    {
        $posts = $this->getEntityManager()
            ->createQuery('select u from MdkybWebsiteBundle:Member u order by u.name asc')
            ->setMaxResults(static::MEMBERS_PER_PAGE)
            ->setFirstResult(max(0, ((int)$page) - 1) * static::MEMBERS_PER_PAGE)
            ->getResult();

        $count = $this->getEntityManager()
            ->createQuery('select count(u) from MdkybWebsiteBundle:Member u')
            ->getSingleScalarResult();

        $pageCount = ceil($count / static::MEMBERS_PER_PAGE);

        return array('users' => $posts, 'page' => $page, 'show_next' => $page < $pageCount);
    }

    /**
     * Shows a member
     * 
     * @Route("/mitglied/{id}", name="show_member")
     * @Template()
     */
    public function showProfileAction($id)
    {
        $user = $this->getEntityManager()->getRepository('MdkybWebsiteBundle:Member')->find($id);
        if ($user == null) {
            throw $this->createNotFoundException('Die Seite existiert nicht!');
        }

        return array('user' => $user);
    }

    /**
     * Shows the membership application form
     * 
     * @Template()
     * @Route("/mitgliedschaft", name="application")
     */
    public function showMembershipApplicationAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        $application = new MembershipApplication();
        $form = $this->createForm(new MembershipApplicationType, $application);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($application);
                $em->flush();

                $request->getSession()->setFlash('member.application', true);
                return $this->redirect($this->generateUrl('application'));
            }
        }

        return array('form' => $form->createView());
    }
}

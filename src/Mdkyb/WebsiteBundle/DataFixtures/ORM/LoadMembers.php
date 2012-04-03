<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Mdkyb\WebsiteBundle\Entity\Member;

class LoadMembers extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('security.encoder_factory');

        $admin = new Member();
        $admin->setName('Administrator');
        $admin->setEmail('admin@mdkyb.dev');
        
        $encoder = $factory->getEncoder($admin);
        $password = $encoder->encodePassword('adminpw', $admin->getSalt());
        $admin->setPassword($password);

        $test = new Member();
        $test->setName('Test');
        $test->setEmail('test@mdkyb.dev');

        $encoder = $factory->getEncoder($test);
        $password = $encoder->encodePassword('testpw', $test->getSalt());
        $test->setPassword($password);

        $manager->persist($admin);
        $manager->persist($test);

        $manager->flush();

        $this->addReference('admin-member', $admin);
        $this->addReference('test-member', $test);
    }

    public function getOrder()
    {
        return 1;
    }
}

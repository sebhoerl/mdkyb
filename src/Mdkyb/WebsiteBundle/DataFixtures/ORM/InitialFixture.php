<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class InitialFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // empty fixture (yet)
    }
}

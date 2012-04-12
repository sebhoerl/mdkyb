<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Mdkyb\WebsiteBundle\Entity\StaticContent;

/**
 * Loads static page fixtures
 */
class LoadStaticContent implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $a = new StaticContent();
        $a->setName('seite_a');
        $a->setTitle('Seite A');
        $a->setContent('Das ist Seite A! Hier gehts zu <a href="[[seite_b]]">Seite B</a>.');

        $b = new StaticContent();
        $b->setName('seite_b');
        $b->setTitle('Seite B');
        $b->setContent('Das ist Seite B! Hier gehts zu <a href="[[seite_c]]">Seite C</a>.');

        $manager->persist($a);
        $manager->persist($b);

        $manager->flush();
    }
}

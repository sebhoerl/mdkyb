<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Mdkyb\WebsiteBundle\Entity\StaticContent;

class LoadStaticContent implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $index = new StaticContent();
        $index->setId('index');
        $index->setTitle('Startseite');
        $index->setContent('Willkommen! Das ist die Startseite! Hier gehts zu <a href="[[seite_a]]">Seite A</a>.');

        $a = new StaticContent();
        $a->setId('seite_a');
        $a->setTitle('Seite A');
        $a->setContent('Das ist Seite A! Hier gehts zu <a href="[[seite_b]]">Seite B</a>.');

        $b = new StaticContent();
        $b->setId('seite_b');
        $b->setTitle('Seite B');
        $b->setContent('Das ist Seite B! Hier gehts zu <a href="[[seite_c]]">Seite C</a>.');

        $menu = new StaticContent();
        $menu->setId('menu');
        $menu->setTitle('Menu');
        $menu->setContent('Das ist das Menü: <a href="[[_index]]">Startseite</a> <a href="[[_downloads]]">Downloads</a> <a href="[[_jobs]]">Jobs</a> <a href="[[seite_a]]">Seite A</a> <a href="[[seite_b]]">Seite B</a> <a href="[[seite_c]]">Seite C</a>');

        $manager->persist($index);
        $manager->persist($a);
        $manager->persist($b);
        $manager->persist($menu);

        $manager->flush();
    }
}

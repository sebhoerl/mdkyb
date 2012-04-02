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
        $index->setContent('Willkommen! Das ist die Startseite! Hier gehts zu [[seite_a :: Seite A]].');

        $a = new StaticContent();
        $a->setId('seite_a');
        $a->setTitle('Seite A');
        $a->setContent('Das ist Seite A! Hier gehts zu [[seite_b :: Seite B]].');

        $b = new StaticContent();
        $b->setId('seite_b');
        $b->setTitle('Seite B');
        $b->setContent('Das ist Seite B! Hier gehts zu [[seite_c :: Seite C]].');

        $menu = new StaticContent();
        $menu->setId('menu');
        $menu->setTitle('Menu');
        $menu->setContent('Das ist das Menü: [[index :: Startseite]] [[seite_a :: A]] [[seite_b :: B]]');

        $manager->persist($index);
        $manager->persist($a);
        $manager->persist($b);
        $manager->persist($menu);

        $manager->flush();
    }
}

<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Mdkyb\WebsiteBundle\Entity\Job;
use Datetime;

class LoadJobs implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $job1 = new Job();
        $job1->setTitle('Job 1');
        $job1->setDescription('Das ist Job 1');
        $job1->setType('job');
        $job1->setExpiresAt(new Datetime('now + 2 weeks'));

        $job2 = new Job();
        $job2->setTitle('Job 2');
        $job2->setDescription('Das ist Job 2');
        $job2->setType('job');
        $job2->setExpiresAt(new Datetime('now + 3 weeks'));

        $job3 = new Job();
        $job3->setTitle('Praktikum 1');
        $job3->setDescription('bla bla bla bla');
        $job3->setType('training');
        $job3->setExpiresAt(new Datetime('now + 4 weeks'));

        $job4 = new Job();
        $job4->setTitle('Prakitkium 2');
        $job4->setDescription('blub blub blab bla');
        $job4->setType('training');
        $job4->setExpiresAt(new Datetime('now + 5 weeks'));

        $manager->persist($job1);
        $manager->persist($job2);
        $manager->persist($job3);
        $manager->persist($job4);

        $manager->flush();
    }
}

<?php

namespace Mdkyb\WebsiteBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Datetime;

use Mdkyb\WebsiteBundle\Entity\BlogPost;

/**
 * Loads blog post fixtures
 */
class LoadBlogPosts extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $post1 = new BlogPost();
        $post1->setTitle('Blog Post Eins');
        $post1->setContent('Das ist ein kleienr Test.');
        $post1->setPublishedAt(new Datetime('now -1 days'));
        $post1->setCreatedAt(new Datetime('now -3 days'));

        $post2 = new BlogPost();
        $post2->setTitle('Blog Post Zwei');
        $post2->setContent('Das ist ein kleienr Test.');
        $post2->setPublishedAt(new Datetime('now -20 minutes'));
        $post2->setCreatedAt(new Datetime('now -20 minutes'));

        $manager->persist($post1);
        $manager->persist($post2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}

<?php

namespace Mdkyb\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Datetime;

class BlogController extends AbstractController
{
    const POSTS_PER_PAGE = 10;

    /**
     * @Route("/", name="index")
     * @Route("/blog")
     * @Route("/blog/page/{page}", name="blog_page")
     * @Template()
     */
    public function pageAction($page = 1)
    {
        $posts = $this->getEntityManager()
            ->createQuery('select p from MdkybWebsiteBundle:BlogPost p where p.publishedAt < :now order by p.publishedAt desc')
            ->setMaxResults(static::POSTS_PER_PAGE)
            ->setFirstResult(max(0, ((int)$page) - 1) * static::POSTS_PER_PAGE)
            ->setParameter('now', new Datetime('now'))
            ->getResult();

        $count = $this->getEntityManager()
            ->createQuery('select count(p) from MdkybWebsiteBundle:BlogPost p')
            ->getSingleScalarResult();

        $pageCount = ceil($count / static::POSTS_PER_PAGE);

        return array('posts' => $posts, 'page' => $page, 'show_next' => $page < $pageCount);
    }
}

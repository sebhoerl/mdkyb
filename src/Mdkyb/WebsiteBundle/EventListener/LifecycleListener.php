<?php

namespace Mdkyb\WebsiteBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Mdkyb\WebsiteBundle\Entity\BlogPost;

class LifecycleListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        $securityContext = $this->container->get('security.context');

        if ($entity instanceof BlogPost && $entity->getAuthor() == null) {
            $token = $securityContext->getToken();
            if ($token && $user = $token->getUser()) {
                $entity->setAuthor($user);
            }
        }
    }
}

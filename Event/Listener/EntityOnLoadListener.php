<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;

class EntityOnLoadListener implements EventSubscriber
{
    /**
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    public function __construct(EntityFactoryInterface $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity) {
            $this->entityFactory->injectManager($entity);
        }
    }
}

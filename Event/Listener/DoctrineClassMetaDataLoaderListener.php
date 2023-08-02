<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class DoctrineClassMetaDataLoaderListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $entityOverride;

    public function __construct(array $entityOverride)
    {
        $this->entityOverride = $entityOverride;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (\in_array($classMetadata->getName(), $this->entityOverride, true)) {
            $classMetadata->isMappedSuperclass = true;
            $classMetadata->table = null;
        }
    }
}

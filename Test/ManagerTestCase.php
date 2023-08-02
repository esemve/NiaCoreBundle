<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Test;

use Nia\CoreBundle\Service\NiaLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class ManagerTestCase extends DBTestCase
{
    abstract protected function getManagerClass(): string;

    abstract protected function getEntityClass(): string;

    protected function createManager()
    {
        $class = $this->getManagerClass();

        $manager = new $class(
            $this->getEntityClass(),
            [$this->getUnitTestQueryFilter()],
            $this->getContainer()->get('nia.core.entity.manager'),
            $this->getContainer()->get('nia.core.entity.factory'),
            $this->getContainer()->get('nia.core.entity.reference.factory'),
            $this->getContainer()->get('nia.core.collection.factory'),
            $this->getContainer()->get('nia.core.enum.factory'),
            $this->getContainer()->get('nia.core.locale.provider'),
            $this->getContextFactory(),
            $this->getContainer()->get('nia.core.cache.provider'),
            $this->getNiaLoggerMock()
        );

        if (method_exists($manager, 'setEventDispatcher')) {
            $eventDispatcherMock = $this->createMock(EventDispatcher::class);
            $eventDispatcherMock->expects($this->any())->method('dispatch');
            $manager->setEventDispatcher($eventDispatcherMock);
        }

        return $manager;
    }

    private function getNiaLoggerMock(): NiaLogger
    {
        return $this->createMock(NiaLogger::class);
    }
}

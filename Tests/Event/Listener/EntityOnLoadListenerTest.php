<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Event\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Event\Listener\EntityOnLoadListener;
use Nia\CoreBundle\Test\TestCase;

class EntityOnLoadListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $listener = new EntityOnLoadListener(
            $this->createMock(EntityFactoryInterface::class)
        );

        $this->assertTrue(\is_array($listener->getSubscribedEvents()));
    }

    public function testPostLoad(): void
    {
        $lc = $this->createMock(LifecycleEventArgs::class);
        $lc->expects($this->once())->method('getObject')->willReturn(new Migration());

        $efMock = $this->createMock(EntityFactoryInterface::class);
        $efMock->expects($this->once())->method('injectManager');

        $listener = new EntityOnLoadListener($efMock);

        $listener->postLoad($lc);
    }
}

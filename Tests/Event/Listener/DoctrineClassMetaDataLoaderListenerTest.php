<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Event\Listener;

use Nia\CoreBundle\Event\Listener\DoctrineClassMetaDataLoaderListener;
use Nia\CoreBundle\Test\TestCase;

class DoctrineClassMetaDataLoaderListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $listener = new DoctrineClassMetaDataLoaderListener([]);

        $this->assertTrue(\is_array($listener->getSubscribedEvents()));
    }

    public function testLoadClassMetadata(): void
    {
        $metadata = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadata::class);
        $metadata->expects($this->once())->method('getName')->willReturn('xxx');

        $args = $this->createMock(\Doctrine\ORM\Event\LoadClassMetadataEventArgs::class);
        $args->expects($this->once())->method('getClassMetaData')->willReturn($metadata);

        $listener = new DoctrineClassMetaDataLoaderListener(['xxx']);
        $listener->loadClassMetadata($args);

        $this->assertTrue($metadata->isMappedSuperclass);
        $this->assertNull($metadata->table);
    }
}

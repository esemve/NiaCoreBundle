<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager\Factory;

use Doctrine\ORM\Mapping\Entity;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Test\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ManagerFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = $this->createManagerFactory();

        $this->assertSame('PatchLogManager', $factory->create(Migration::class));
    }

    public function testSetMapping(): void
    {
        $factory = $this->createEmptyManagerFactory();
        $factory->setManagerMapping(['PatchLogManager' => Migration::class]);

        $this->assertSame('PatchLogManager', $factory->create(Migration::class));
    }

    public function testCreateByEntity(): void
    {
        $factory = $this->createManagerFactory();

        $this->assertSame('PatchLogManager', $factory->createByEntity(new Migration()));
    }

    /**
     * @expectedException \Nia\CoreBundle\Exception\InvalidConfigrationException
     */
    public function testNotFoundByCreate(): void
    {
        $factory = $this->createManagerFactory();
        $factory->create('xxx');
    }

    /**
     * @expectedException \Nia\CoreBundle\Exception\InvalidConfigrationException
     */
    public function testNotFoundByCreateByEntity(): void
    {
        $factory = $this->createEmptyManagerFactory();
        $factory->createByEntity(new Migration());
    }

    protected function createManagerFactory(): ManagerFactory
    {
        return new ManagerFactory(
            ['PatchLogManager' => Migration::class],
            $this->createMockContainer()
        );
    }

    protected function createEmptyManagerFactory(): ManagerFactory
    {
        return new ManagerFactory(
            ['xxxManager' => Entity::class],
            $this->createMockContainer()
        );
    }

    protected function createMockContainer(): Container
    {
        $container = $this->createMock(Container::class);
        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($name) { return $name; }));

        return $container;
    }
}

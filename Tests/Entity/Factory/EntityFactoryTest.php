<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Entity\Factory;

use Nia\CoreBundle\Entity\Factory\EntityFactory;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Test\TestCase;

class EntityFactoryTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetEntityClassName(array $entityOverride): void
    {
        $factory = $this->createEntityFactory($entityOverride);

        $element = \array_slice($entityOverride, 0, 1);
        $this->assertSame(array_values($element)[0], $factory->getEntityClassName(array_values($element)[0]));

        $element = \array_slice($entityOverride, 1, 1);
        $this->assertSame(array_values($element)[0], $factory->getEntityClassName(array_values($element)[0]));

        $this->assertSame('Nia\Entity\Test3', 'Nia\Entity\Test3');
    }

    public function dataProvider(): array
    {
        return [
            [
                [
                    'Nia\Entity\Test1' => 'Nia\NewEntity\Test1',
                    'Nia\Entity\Test2' => 'Nia\NewEntity\Test2',
                ],
            ],
        ];
    }

    protected function createEntityFactory(array $entityOverride): EntityFactoryInterface
    {
        return new EntityFactory($entityOverride, $this->getManagerFactory());
    }

    protected function getManagerFactory(): ManagerFactory
    {
        return $this->getContainer()->get('nia.core.manager.factory');
    }
}

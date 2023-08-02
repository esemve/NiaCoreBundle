<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Manager\RunLogManager;
use Nia\CoreBundle\Test\DBTestCase;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;

class RunLogManagerTest extends DBTestCase
{
    public function testGetTime(): void
    {
        $manager = $this->createManager();

        $this->assertSame('2019-03-10 11:00:00', $manager->getTime('test')->format('Y-m-d H:i:s'));
        $this->assertSame('2019-03-10 12:00:00', $manager->getTime('test2')->format('Y-m-d H:i:s'));
    }

    public function testUpdate(): void
    {
        $manager = $this->createManager();
        $originalTime = $manager->getTime('test')->format('Y-m-d H:i:s');

        $manager->update('test');
        $testEntity = $manager->getTime('test');
        $this->assertNotSame($originalTime, $testEntity->format('Y-m-d H:i:s'));
        $this->assertCount(2, $manager->findAll());

        $newTestEntity = $manager->getTime('test3');
        $this->assertNull($newTestEntity);
        $manager->update('test3');
        $newTestEntity = $manager->getTime('test3');
        $this->assertInstanceOf(\DateTimeInterface::class, $newTestEntity);
        $this->assertCount(3, $manager->findAll());
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return new ArrayDataSet(require __DIR__.'/fixtures/runLogManagerTestDataSet.php');
    }

    protected function createManager(): RunLogManager
    {
        return new RunLogManager(
            $this->getEntityManager()
        );
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getContainer()->get('nia.core.entity.manager');
    }
}

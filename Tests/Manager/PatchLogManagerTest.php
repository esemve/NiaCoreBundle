<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Manager\MigrationManager;
use Nia\CoreBundle\Test\ManagerTestCase;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;

class PatchLogManagerTest extends ManagerTestCase
{
    public function testSaveList(): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $originalCount = $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context);

        $entity1 = $this->fillEntityWithDefaultDatas($manager->createNew());
        $entity2 = $this->fillEntityWithDefaultDatas($manager->createNew());

        $manager->saveList($this->createEntityCollection([$entity1, $entity2]), $context);

        $this->assertSame($originalCount + 2, $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context));
    }

    public function testRemove(): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $entity = $this->fillEntityWithDefaultDatas($manager->createNew());
        $originalCount = $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context);
        $manager->save($entity, $context);
        $this->assertSame($originalCount + 1, $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context));
        $manager->remove($entity, $context);
        $this->assertSame($originalCount, $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context));
    }

    public function testTransaction(): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $originalCount = $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context);

        $manager->beginTransaction();
        $entity = $this->fillEntityWithDefaultDatas($manager->createNew());
        $manager->save($entity, $context);
        $manager->rollBack();
        $this->assertSame($originalCount, $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context));

        $manager->beginTransaction();
        $entity = $this->fillEntityWithDefaultDatas($manager->createNew());
        $manager->save($entity, $context);
        $manager->commit();
        $this->assertSame($originalCount + 1, $manager->countBy($this->createCriteriaCollection([['id', '>', 0]]), $context));
    }

    private function fillEntityWithDefaultDatas(Entity $entity): Entity
    {
        $refObject = new \ReflectionObject($entity);
        foreach ($refObject->getProperties() as $property) {
            $comment = str_replace(' ', '', $property->getDocComment());

            if (false !== mb_strpos($comment, 'nullable=true')) {
                continue;
            }

            if (false === mb_strpos($comment, '@ORM')) {
                continue;
            }

            if ((false !== mb_strpos($comment, 'type="string"')) || (false !== mb_strpos($comment, 'type="string"'))) {
                $property->setAccessible(true);
                $property->setValue($entity, md5((string) rand(1, 99000000)));
                continue;
            }

            if (false !== mb_strpos($comment, 'type="int"')) {
                $property->setAccessible(true);
                $property->setValue($entity, rand(1, 990000000));
                continue;
            }
        }

        return $entity;
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return new ArrayDataSet(require __DIR__.'/fixtures/managerTestDataSet.php');
    }

    protected function getManagerClass(): string
    {
        return MigrationManager::class;
    }

    protected function getEntityClass(): string
    {
        return Migration::class;
    }
}

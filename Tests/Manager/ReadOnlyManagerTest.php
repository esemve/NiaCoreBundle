<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Esemve\Collection\IntegerCollection;
use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Enum\OrderEnum;
use Nia\CoreBundle\Manager\ReadOnlyManager;
use Nia\CoreBundle\Test\ManagerTestCase;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;

class ReadOnlyManagerTest extends ManagerTestCase
{
    /**
     * @dataProvider dataProviderFindOne
     */
    public function testFindOneById(int $id, string $log): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        /* @var Migration $entity */
        $entity = $manager->findById($id, $context);

        $this->assertSame($id, $entity->getId());
        $this->assertSame($log, $entity->getMigration());
    }

    public function dataProviderFindOne(): array
    {
        return [
            [1, 'test1'],
            [2, 'test2'],
            [3, 'test3'],
        ];
    }

    /**
     * @dataProvider dataProviderFindOneException
     * @expectedException \Nia\CoreBundle\Exception\NotFoundException
     */
    public function testFindOneByIdException(int $id): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $manager->findById($id, $context);
    }

    public function dataProviderFindOneException(): array
    {
        return [
            [1000, null],
            [5000, null],
        ];
    }

    /**
     * @dataProvider dataProviderFindOneByCriteria
     */
    public function testOneByCriteria(array $criteria, int $id): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        /* @var Migration $entity */
        $entity = $manager->findOneBy($this->createCriteriaCollection($criteria), $context);

        $this->assertSame($id, $entity->getId());
    }

    public function dataProviderFindOneByCriteria(): array
    {
        return [
            [[['id', '=', 1]], 1],
            [[['migration', '=', 'test2'], ['id', '=', 2]], 2],
            [[['migration', '%LIKE%', '3']], 3],
        ];
    }

    /**
     * @dataProvider dataProviderFindOneByCriteriaException
     * @expectedException \Nia\CoreBundle\Exception\NotFoundException
     */
    public function testOneByCriteriaException(array $criteria): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $manager->findOneBy($this->createCriteriaCollection($criteria), $context);
    }

    public function dataProviderFindOneByCriteriaException(): array
    {
        return [
            [[['id', '=', 1000]]],
            [[['migration', '%LIKE%', '3'], ['id', '>', 300]]],
        ];
    }

    /**
     * @dataProvider dataProviderCountByCriteria
     */
    public function testCountByCriteria(array $criteria, int $count): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        $found = $manager->countBy($this->createCriteriaCollection($criteria), $context);

        $this->assertSame($count, $found);
    }

    public function dataProviderCountByCriteria(): array
    {
        return [
            [[['id', '=', 1]], 1],
            [[['migration', '%LIKE%', 'test']], 4],
            [[['migration', 'LIKE%', 'te']], 4],
            [[['migration', '%LIKE', 'st4']], 1],
            [[['migration', '=', 'xxx']], 0],
        ];
    }

    /**
     * @dataProvider dataProviderFindAllByIds
     */
    public function testFindAllByIds(IntegerCollection $ids): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        /* @var Migration $entity */
        $found = $manager->findAllByIds($ids, $context);

        $this->assertInstanceOf(EntityCollection::class, $found);
        $this->assertSame($ids->count(), $found->count());
    }

    public function dataProviderFindAllByIds(): array
    {
        return [
            [new IntegerCollection([1, 2])],
            [new IntegerCollection([])],
            [new IntegerCollection([1, 2, 3])],
        ];
    }

    /**
     * @dataProvider dataProviderFindAllBy
     */
    public function testFindAllBy(array $criteria, array $expectedIds, $orderBy = 'id', $order = null, ?int $limit = null, ?int $offset = 0): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();
        /* @var Migration $entity */
        $found = $manager->findAllBy($this->createCriteriaCollection($criteria), $context, $orderBy, $order, $limit, $offset);

        $this->assertInstanceOf(EntityCollection::class, $found);
        $this->assertSame(\count($expectedIds), $found->count());

        $ids = [];
        foreach ($found as $elem) {
            $ids[] = $elem->getId();
        }

        $this->assertSame($expectedIds, $ids);
    }

    public function dataProviderFindAllBy(): array
    {
        return [
            [[['id', '=', 1]], [1]],
            [[['migration', '=', 'test2'], ['id', '=', 2]], [2]],
            [[['migration', '%LIKE%', '3']], [3]],
            [[['migration', 'LIKE', '%test%']], [1, 2, 3, 4], 'id', new OrderEnum('asc')],
            [[['migration', 'LIKE', '%test%']], [4, 3, 2, 1], 'id', new OrderEnum('desc')],
            [[['migration', 'LIKE', '%test%']], [4, 3], 'id', new OrderEnum('desc'), 2],
            [[['migration', 'LIKE', '%test%']], [2, 1], 'id', new OrderEnum('desc'), 2, 2],
            [[['migration', 'LIKE', '%xxxx%']], []],
        ];
    }

    public function testOrderByAsc(): void
    {
        $manager = $this->createManager();

        $enum = new OrderEnum('asc');

        $this->assertSame($enum->getValue(), $manager->orderByAsc()->getValue());
    }

    public function testOrderByDesc(): void
    {
        $manager = $this->createManager();

        $enum = new OrderEnum('desc');

        $this->assertSame($enum->getValue(), $manager->orderByDesc()->getValue());
    }

    public function testRefresh(): void
    {
        $context = $this->createContext();

        $manager = $this->createManager();

        $entity = $manager->findById(1, $context);

        $this->assertSame($entity, $manager->refresh(clone $entity, $context));
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet(): IDataSet
    {
        return new ArrayDataSet(require __DIR__.'/fixtures/managerTestDataSet.php');
    }

    protected function getManagerClass(): string
    {
        return ReadOnlyManager::class;
    }

    protected function getEntityClass(): string
    {
        return Migration::class;
    }
}

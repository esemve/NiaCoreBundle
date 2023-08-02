<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\UndeletableEntityInterface;
use Nia\CoreBundle\Security\QueryFilters\AbstractQueryFilter;
use Nia\CoreBundle\Security\QueryFilters\UndeletableFilter;
use Nia\CoreBundle\Test\QueryFilterTestCase;

class UndeletableFilterTest extends QueryFilterTestCase
{
    public function testIsSupported(): void
    {
        $filter = $this->getFilter();

        $this->assertFalse($filter->isSupported($this->createMockEntity()));

        $entity = $this->createMock([Entity::class, UndeletableEntityInterface::class]);

        $this->assertTrue($filter->isSupported($entity));
    }

    public function testGetFilter(): void
    {
        $filter = $this->getFilter()->getFilter(null, $this->createContext());

        $this->assertNull($filter);
    }

    public function testPositiveCanEdit(): void
    {
        // SoftDeleteQueryFilter dont use this method
        $this->assertTrue(true);
    }

    public function testPositiveCanDelete(): void
    {
        $filter = $this->getFilter();

        $entity = $this->createMock([Entity::class, UndeletableEntityInterface::class]);
        $entity->expects($this->any())->method('isUndeletable')->willReturn(false);

        $this->assertTrue($filter->canDelete($entity, $this->createContext()));
    }

    public function testPositiveCanCreate(): void
    {
        // SoftDeleteQueryFilter dont use this method
        $this->assertTrue(true);
    }

    public function testNegativeCanEdit(): void
    {
        $filter = $this->getFilter();
        $this->assertNull($filter->canEdit($this->createMockEntity(), $this->createContext()));
    }

    public function testNegativeCanDelete(): void
    {
        $filter = $this->getFilter();

        $entity = $this->createMock([Entity::class, UndeletableEntityInterface::class]);
        $entity->expects($this->any())->method('isUndeletable')->willReturn(true);

        $this->assertFalse($filter->canDelete($entity, $this->createContext()));
    }

    public function testNegativeCanCreate(): void
    {
        $filter = $this->getFilter();
        $this->assertNull($filter->canCreate($this->createMockEntity(), $this->createContext()));
    }

    public function getFilter(): AbstractQueryFilter
    {
        return new UndeletableFilter();
    }
}

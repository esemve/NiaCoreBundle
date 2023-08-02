<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Nia\CoreBundle\Security\QueryFilters\AbstractQueryFilter;
use Nia\CoreBundle\Security\QueryFilters\FullAccessFilter;
use Nia\CoreBundle\Test\QueryFilterTestCase;

class FullAccessFilterTest extends QueryFilterTestCase
{
    public function testIsSupported(): void
    {
        $filter = $this->getFilter();

        $this->assertTrue($filter->isSupported($this->createMockEntity()));
    }

    public function testGetFilter(): void
    {
        $filter = $this->getFilter();
        $this->assertNull($filter->getFilter('aa', $this->createContext()));
        $this->assertNull($filter->getFilter('bb', $this->createContext()));
    }

    public function testPositiveCanEdit(): void
    {
        $filter = $this->getFilter();

        $this->assertTrue($filter->canEdit($this->createMockEntity(), $this->createContext()));
    }

    public function testPositiveCanDelete(): void
    {
        $filter = $this->getFilter();

        $this->assertTrue($filter->canDelete($this->createMockEntity(), $this->createContext()));
    }

    public function testPositiveCanCreate(): void
    {
        $filter = $this->getFilter();

        $this->assertTrue($filter->canCreate($this->createMockEntity(), $this->createContext()));
    }

    public function testNegativeCanEdit(): void
    {
        // Full access filter allow anything!
        $this->assertTrue(true);
    }

    public function testNegativeCanDelete(): void
    {
        // Full access filter allow anything!
        $this->assertTrue(true);
    }

    public function testNegativeCanCreate(): void
    {
        // Full access filter allow anything!
        $this->assertTrue(true);
    }

    public function getFilter(): AbstractQueryFilter
    {
        return new FullAccessFilter();
    }
}

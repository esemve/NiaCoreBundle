<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Nia\CoreBundle\Security\QueryFilters\RoleFilter;
use Nia\CoreBundle\Test\QueryFilterTestCase;

class RoleFilterTest extends QueryFilterTestCase
{
    public function testIsSupported(): void
    {
        $filter = $this->createFilter();

        $this->assertTrue($filter->isSupported($this->createMockEntity()));
    }

    public function testGetFilter(): void
    {
        $this->assertNull($this->createFilter()->getFilter(null, $this->createContext(['ROLE_TEST_SHOW'])));

        $filter = $this->createFilter()->getFilter(null, $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_EDIT', 'ROLE_TEST_DELETE']));
        $this->assertInstanceOf(Andx::class, $filter);
        $this->assertCount(1, $filter->getParts());
        $this->assertSame('1 = 2', $filter->getParts()[0]);
    }

    public function testPositiveCanEdit(): void
    {
        $this->assertTrue($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE', 'ROLE_TEST_EDIT'])));
    }

    public function testNegativeCanEdit(): void
    {
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE'])));
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE'])));
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext(['ROLE_TEST_ROLE_ANOTHER_EDIT'])));
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext([''])));
        $this->assertFalse($this->createFilter()->canEdit($this->createMockEntity(), $this->createContext()));
    }

    public function testPositiveCanDelete(): void
    {
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT', 'ROLE_TEST_DELETE'])));
    }

    public function testNegativeCanDelete(): void
    {
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE'])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT'])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_ANOTHER_DELETE'])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext([''])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext()));
    }

    public function testPositiveCanCreate(): void
    {
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE'])));
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_CREATE'])));
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT', 'ROLE_TEST_CREATE'])));
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE', 'ROLE_TEST_CREATE'])));
    }

    public function testNegativeCanCreate(): void
    {
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_ANOTHER_CREATE'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext([''])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext()));
    }

    protected function createFilter(): RoleFilter
    {
        $filter = new RoleFilter();
        $filter->isSupported($this->createMockEntity());

        return $filter;
    }
}

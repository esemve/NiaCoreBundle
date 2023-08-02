<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Nia\CoreBundle\Security\QueryFilters\CreateAndDeleteGrantIfHasEditRole;
use Nia\CoreBundle\Security\QueryFilters\RoleFilter;

class CreateAndDeleteGrantIfHasEditRoleTest extends RoleFilterTest
{
    public function testPositiveCanDelete(): void
    {
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT', 'ROLE_TEST_DELETE'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_EDIT'])));
    }

    public function testNegativeCanDelete(): void
    {
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertFalse($this->createFilter()->canDelete($this->createMockEntity(), $this->createContext(['ROLE_TEST_CREATE'])));
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
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW', 'ROLE_TEST_EDIT'])));
        $this->assertTrue($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE', 'ROLE_TEST_EDIT'])));
    }

    public function testNegativeCanCreate(): void
    {
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_TEST_DELETE'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext(['ROLE_ANOTHER_CREATE'])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext([''])));
        $this->assertFalse($this->createFilter()->canCreate($this->createMockEntity(), $this->createContext()));
    }

    protected function createFilter(): RoleFilter
    {
        $filter = new CreateAndDeleteGrantIfHasEditRole();
        $filter->isSupported($this->createMockEntity());

        return $filter;
    }
}

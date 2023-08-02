<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Nia\CoreBundle\Security\QueryFilters\ReadOnlyFilter;
use Nia\CoreBundle\Security\QueryFilters\RoleFilter;

class ReadOnlyFilterTest extends RoleFilterTest
{
    public function testGetFilter(): void
    {
        $this->assertNull($this->createFilter()->getFilter(null, $this->createContext(['ROLE_TEST_SHOW'])));
        $this->assertNull($this->createFilter()->getFilter(null, $this->createContext(['ROLE_TEST_CREATE', 'ROLE_TEST_EDIT', 'ROLE_TEST_DELETE'])));
        $this->assertNull($this->createFilter()->getFilter(null, $this->createContext()));
    }

    protected function createFilter(): RoleFilter
    {
        $filter = new ReadOnlyFilter();
        $filter->isSupported($this->createMockEntity());

        return $filter;
    }
}

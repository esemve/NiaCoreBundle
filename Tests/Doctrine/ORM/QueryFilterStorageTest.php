<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Doctrine\ORM;

use Nia\CoreBundle\Doctrine\ORM\QueryFilterStorage;
use Nia\CoreBundle\Test\TestCase;

class QueryFilterStorageTest extends TestCase
{
    /**
     * @expectedException \Nia\CoreBundle\Exception\InvalidConfigrationException
     */
    public function testEmptyFiltersThrowException(): void
    {
        QueryFilterStorage::getFilters('dasd');
    }

    public function filtersTest(): void
    {
        QueryFilterStorage::setFilters('xx', [1, 2, 3]);
        QueryFilterStorage::setFilters('zz', [3, 4, 5]);

        $this->assertSame([1, 2, 3], QueryFilterStorage::getFilters('xx'));
        $this->assertSame([3, 4, 5], QueryFilterStorage::getFilters('zz'));
    }
}

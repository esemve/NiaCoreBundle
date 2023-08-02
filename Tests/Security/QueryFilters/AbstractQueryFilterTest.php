<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\CoreBundle\Security\QueryFilters\RoleFilter;
use Nia\CoreBundle\Test\TestCase;

class AbstractQueryFilterTest extends TestCase
{
    public function testAliasCheck(): void
    {
        $context = $this->createContext();

        $filter = $this->createFilter('test');
        $this->assertInstanceOf(Andx::class, $filter->getFilter('alias', $context));
    }

    protected function createFilter(): RoleFilter
    {
        $filter = new RoleFilter();
        $filter->isSupported($this->createMockEntity());

        return $filter;
    }

    protected function createMockEntity(): Entity
    {
        $mockManager = $this->createMock(AbstractManager::class);
        $mockManager->expects($this->any())->method('getRoleGroup')->willReturn('TEST');

        $entity = $this->createMock(Entity::class);
        $entity->expects($this->any())->method('getManager')->willReturn($mockManager);

        return $entity;
    }
}

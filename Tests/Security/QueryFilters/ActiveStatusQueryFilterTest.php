<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\StatusableInterace;
use Nia\CoreBundle\Security\QueryFilters\AbstractQueryFilter;
use Nia\CoreBundle\Security\QueryFilters\ActiveStatusQueryFilter;
use Nia\CoreBundle\Test\QueryFilterTestCase;

class ActiveStatusQueryFilterTest extends QueryFilterTestCase
{
    public function testIsSupported(): void
    {
        $filter = $this->getFilter();

        $this->assertFalse($filter->isSupported($this->createMockEntity()));

        $entity = $this->createMock([Entity::class, StatusableInterace::class]);

        $this->assertTrue($filter->isSupported($entity));
    }

    public function testGetFilter(): void
    {
        $context = $this->createContext();

        $filter = $this->getFilter()->getFilter(null, $context);

        $this->assertInstanceOf(Andx::class, $filter);
        $this->assertCount(1, $filter->getParts());
        $this->assertSame('status = 1', $filter->getParts()[0]);
    }

    public function testPositiveCanEdit(): void
    {
        // ActiveStatusQueryFilter dont use this method
        $this->assertTrue(true);
    }

    public function testPositiveCanDelete(): void
    {
        // ActiveStatusQueryFilter dont use this method
        $this->assertTrue(true);
    }

    public function testPositiveCanCreate(): void
    {
        // ActiveStatusQueryFilter dont use this method
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
        $this->assertNull($filter->canDelete($this->createMockEntity(), $this->createContext()));
    }

    public function testNegativeCanCreate(): void
    {
        $filter = $this->getFilter();
        $this->assertNull($filter->canCreate($this->createMockEntity(), $this->createContext()));
    }

    public function getFilter(): AbstractQueryFilter
    {
        return new ActiveStatusQueryFilter();
    }
}

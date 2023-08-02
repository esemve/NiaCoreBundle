<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Constraint;

use Nia\CoreBundle\Constraint\UniqueField;
use Nia\CoreBundle\Test\TestCase;

class UniqueFieldTest extends TestCase
{
    public function testGetRequiredOptions(): void
    {
        $this->assertSame(['entity', 'field', 'context'], $this->createConstraint()->getRequiredOptions());
    }

    public function testGetTargets(): void
    {
        $this->assertSame('class', $this->createConstraint()->getTargets());
    }

    protected function createConstraint(): UniqueField
    {
        return new UniqueField(['field' => 'testProperty', 'context' => $this->createContext(), 'entity' => 'name']);
    }
}

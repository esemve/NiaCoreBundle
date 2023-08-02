<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Constraint;

use Nia\CoreBundle\Constraint\UniqueField;
use Nia\CoreBundle\Constraint\UniqueFieldValidator;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Repository\AbstractRepository;
use Nia\CoreBundle\Test\TestCase;

class UniqueFieldValidatorTest extends TestCase
{
    public function testEmptyValue(): void
    {
        $validator = $this->createConstraintValidator();
        $this->assertTrue($validator->validate(null, $this->createConstraint()));
    }

    /**
     * @expectedException \Nia\CoreBundle\Exception\InvalidConfigrationException
     */
    public function testWrongRepository(): void
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('getRepository')->willReturn(new \stdClass());

        $validator = $this->createConstraintValidator($em);
        $validator->validate('xx', $this->createConstraint());
    }

    public function testNotFoundException(): void
    {
        $repository = $this->createMock(AbstractRepository::class);
        $repository->expects($this->once())->method('findOneUniqueExceptId')->willReturn(null);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('getRepository')->willReturn($repository);

        $validator = $this->createConstraintValidator($em);
        $this->assertTrue($validator->validate('xx', $this->createConstraint()));
    }

    protected function createConstraintValidator(EntityManager $em = null): UniqueFieldValidator
    {
        $validator = new UniqueFieldValidator();
        if (!empty($em)) {
            $validator->setEntityManager($em);
        }

        return $validator;
    }

    protected function createConstraint(): UniqueField
    {
        return new UniqueField(['field' => 'patch', 'context' => $this->createContext(), 'entity' => new Migration()]);
    }
}

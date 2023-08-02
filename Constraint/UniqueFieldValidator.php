<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Constraint;

use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Nia\CoreBundle\Repository\AbstractRepository;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFieldValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setContextFactory(ContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        $context = $constraint->context;

        $field = $constraint->field;

        if (empty($value)) {
            return true;
        }

        $repository = $this->entityManager->getRepository(\get_class($constraint->entity));

        if (!($repository instanceof AbstractRepository)) {
            throw new InvalidConfigrationException(\get_class($repository).' must be extend from AbstractRepository!');
        }

        $found = $repository->findOneUniqueExceptId($field, $value, $constraint->entity->getId(), $context);

        if (null === $found) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();

        return true;
    }
}

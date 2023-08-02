<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;

abstract class AbstractRepository extends EntityRepository
{
    protected $changedEntityManager = false;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    public function setManager(EntityManagerInterface $em): EntityRepository
    {
        if (false === $this->changedEntityManager) {
            $this->_em = $em;
            $this->changedEntityManager = true;
        }

        return $this;
    }

    public function findOneUniqueExceptId(string $field, $value, ?int $excepdId, Context $context): ?Entity
    {
        $qb = $this->createQueryBuilderWithContext($context, 'u');
        if (empty($excepdId)) {
            $excepdId = 0;
        }
        $qb->where('u.id != :id')->andWhere('u.'.$field.' = :value');
        $qb->setParameter('value', $value)
           ->setParameter('id', $excepdId);
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    final public function createQueryBuilder($alias, $indexBy = null)
    {
        throw new \Exception('Please use createQueryBuilderWithContext method insted of this function!');
    }

    public function createQueryBuilderWithContext(Context $context, string $alias, $indexBy = null): QueryBuilder
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy);

        if (method_exists($queryBuilder, 'setContext')) {
            $queryBuilder->setContext($context);
        }

        return $queryBuilder;
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Esemve\Collection\IntegerCollection;
use Esemve\Collection\StringCollection;
use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Collections\OrderCollection;
use Nia\CoreBundle\Entity\AbstractEntity;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Enum\OrderEnum;
use Nia\CoreBundle\Exception\NotFoundException;
use Nia\CoreBundle\Repository\AbstractRepository;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\QueryFilters\QueryFilterInterface;

class ReadOnlyManager extends AbstractManager
{
    protected function createCollection(array $collection = []): EntityCollection
    {
        return $this->getCollectionFactory()->create(EntityCollection::class, $collection ?? []);
    }

    public function findById(int $id, Context $context): ?Entity
    {
        $collection = $this->findAllByIds(new IntegerCollection([$id]), $context);

        if (0 === $collection->count()) {
            throw new NotFoundException(
                sprintf('Not found %s entity with id %s!', $this->getEntityClass(), $id)
            );
        }

        return $collection->first();
    }

    public function findOneBy(CriteriaCollection $criteriaCollection, Context $context, $orderBy = null, $order = null): ?Entity
    {
        $collection = $this->findAllBy($criteriaCollection, $context, $orderBy, $order, 1, 0);

        if (0 === $collection->count()) {
            throw new NotFoundException(
                sprintf('Not found %s entity with criteria!', $this->getEntityClass())
            );
        }

        return $collection->first();
    }

    public function countBy(CriteriaCollection $criteriaCollection, Context $context): int
    {
        $builder = $this->createQueryBuilder($context, self::DEFAULT_QUERY_ALIAS);
        $builder->select(sprintf('count(%s.id)', self::DEFAULT_QUERY_ALIAS));
        $this->buildByCriterias($criteriaCollection, $builder);

        return (int) $builder->getQuery()->getSingleScalarResult();
    }

    public function findBigDataBy(?CriteriaCollection $criteriaCollection, Context $context, $orderBy = 'id', $order = null, ?int $limit = null, ?int $offset = 0): IterableResult
    {
        $builder = $this->doBuildFindAllByQuery($criteriaCollection, $context, $orderBy, $order, $limit, $offset);
        $this->addQueryFilterToQueryBuilder($builder, $context);

        return $builder->getQuery()->iterate();
    }

    public function findAllBy(?CriteriaCollection $criteriaCollection, Context $context, $orderBy = 'id', $order = null, ?int $limit = null, ?int $offset = 0): EntityCollection
    {
        $builder = $this->doBuildFindAllByQuery($criteriaCollection, $context, $orderBy, $order, $limit, $offset);

        return $this->findAllByQuery($builder, $context);
    }

    public function findAllByQuery(QueryBuilder $queryBuilder, Context $context): EntityCollection
    {
        $this->addQueryFilterToQueryBuilder($queryBuilder, $context);

        $query = $queryBuilder->getQuery();
        $founds = $query->getResult();
        foreach ($founds as $found) {
            if ($found instanceof AbstractEntity) {
                $found->setManager($this);
            }
        }

        return $this->createCollection($founds);
    }

    public function findOneByQuery(QueryBuilder $queryBuilder, Context $context): ?Entity
    {
        $queryBuilder = $queryBuilder->setMaxResults(1);
        $collection = $this->findAllByQuery($queryBuilder, $context);

        return $collection->first();
    }

    public function findAllByIds(IntegerCollection $ids, Context $context, ?bool $sortByCollection = true): EntityCollection
    {
        $found = $this->findAllByQuery(
            $this->createQueryBuilder($context)
                ->andWhere(self::DEFAULT_QUERY_ALIAS.'.id IN (:ids)')
                ->setParameter('ids', $ids->values()->all()),
            $context
        );

        return $found;
    }

    private function doBuildFindAllByQuery(?CriteriaCollection $criteriaCollection, Context $context, $orderBy, $order, ?int $limit, ?int $offset): QueryBuilder
    {
        if (null === $order) {
            $order = $this->orderByDesc();
        }

        if (null === $orderBy) {
            if ($this->getSampleEntity() instanceof IdentifiableEntityInterface) {
                $orderBy = 'id';
            }
        }

        $builder = $this->createQueryBuilder($context, self::DEFAULT_QUERY_ALIAS);
        if (null !== $criteriaCollection) {
            $this->buildByCriterias($criteriaCollection, $builder);
        }

        $orderArray = [];

        if ($order instanceof OrderCollection) {
            $orderArray = $order->values();
        } else {
            if ($orderBy instanceof StringCollection) {
                for ($i = 0; $i < $orderBy->count(); ++$i) {
                    $orderArray[$i] = $order;
                }
            } else {
                $orderArray[0] = $order;
            }
        }

        if ($orderBy instanceof StringCollection) {
            $i = 0;
            foreach ($orderBy as $subOrderBy) {
                $this->buildOrder($builder, $subOrderBy, $orderArray[$i]->getValue());
                ++$i;
            }
        } else {
            $this->buildOrder($builder, $orderBy, $orderArray[0]->getValue());
        }

        if (null !== $limit) {
            $builder->setMaxResults($limit);
        }
        $builder->setFirstResult($offset);

        return $builder;
    }

    protected function createQuery(string $dql): Query
    {
        return $this->getEntityManager()->createQuery($dql);
    }

    protected function createQueryBuilder(Context $context, string $alias = self::DEFAULT_QUERY_ALIAS): QueryBuilder
    {
        /** @var \Nia\CoreBundle\Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->getEntityManager()
            ->getRepository($this->getEntityClass())
            ->createQueryBuilderWithContext($context, $alias);

        return $queryBuilder;
    }

    protected function getRepository(): AbstractRepository
    {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    public function refresh(IdentifiableEntityInterface $entity, Context $context): IdentifiableEntityInterface
    {
        $this->getEntityManager()->getUnitOfWork()->detach($entity);

        return $this->findById($entity->getId(), $context);
    }

    protected function buildByCriterias(CriteriaCollection $criteriaCollection, QueryBuilder $builder): void
    {
        $i = 0;
        foreach ($criteriaCollection as $criteria) {
            ++$i;
            $field = str_replace('%s', self::DEFAULT_QUERY_ALIAS, $criteria[0]);
            if ($field === $criteria[0]) {
                $field = self::DEFAULT_QUERY_ALIAS.'.'.$criteria[0];
            }

            if (null === $criteria[2]) {
                $criteria[2] = 'null';
            }

            $lowerCaseCriteria2 = mb_strtolower(((string) $criteria[2]));
            $lowerCaseCriteria1 = mb_strtolower(((string) $criteria[1]));

            if (!\is_array($criteria[2])) {
                $criteria[2] = (string) $criteria[2];
            }

            $exp = str_replace('%s', self::DEFAULT_QUERY_ALIAS, ((string) $criteria[2]));
            if ($exp === $criteria[2]) {
                if ('null' === $lowerCaseCriteria2) {
                    $exp = 'null';
                    if ('=' === $criteria[1]) {
                        $criteria[1] = 'IS';
                    }
                } elseif ('now()' === $lowerCaseCriteria2 || 'now' === $lowerCaseCriteria2) {
                    $exp = 'CURRENT_TIMESTAMP()';
                } else {
                    switch ($lowerCaseCriteria1) {
                        case ',like,':
                            $criteria[2] = '%,'.$criteria[2].',%';
                            $criteria[1] = 'LIKE';

                            break;
                        case 'like%':
                            $criteria[2] = $criteria[2].'%';
                            $criteria[1] = 'LIKE';
                            break;
                        case '%like':
                            $criteria[2] = '%'.$criteria[2];
                            $criteria[1] = 'LIKE';
                            break;
                        case '%like%':
                            $criteria[2] = '%'.$criteria[2].'%';
                            $criteria[1] = 'LIKE';
                            break;
                    }

                    $builder->setParameter($criteria[0].$i, $criteria[2]);
                    $exp = ':'.$criteria[0].$i;
                }
            } else {
                $exp = $criteria[2];
            }

            $builder->andWhere($field.' '.$criteria[1].' '.$exp);
        }
    }

    public function orderByAsc(): OrderEnum
    {
        return $this->getEnumFactory()->create(OrderEnum::class, OrderEnum::ASC);
    }

    public function orderByDesc(): OrderEnum
    {
        return $this->getEnumFactory()->create(OrderEnum::class, OrderEnum::DESC);
    }

    protected function buildOrder(QueryBuilder $builder, string $subOrderBy, string $order): void
    {
        $builder->addOrderBy(self::DEFAULT_QUERY_ALIAS.'.'.$subOrderBy, $order);
    }

    public function getAllIdsInDatabaseByIds(IntegerCollection $integerCollection, Context $context): array
    {
        $builder = $this->createQueryBuilder($context, self::DEFAULT_QUERY_ALIAS);
        $builder->select(sprintf('%s.id', self::DEFAULT_QUERY_ALIAS));
        $builder->where(self::DEFAULT_QUERY_ALIAS.'.id IN (:ids)')
        ->setParameter('ids', $integerCollection->values());

        $found = $builder->getQuery()->getScalarResult();

        $output = [];
        foreach ($found as $row) {
            $output[$row['id']] = $row['id'];
        }

        return $output;
    }

    final protected function addQueryFilterToQueryBuilder(QueryBuilder $queryBuilder, Context $context): void
    {
        if (!empty($this->getQueryFilters())) {
            /** @var QueryFilterInterface $queryFilter */
            foreach ($this->getQueryFilters() as $queryFilter) {
                $aliases = $queryBuilder->getRootAliases();
                $alias = reset($aliases);
                $filter = $queryFilter->getFilter($alias, $context);

                $queryBuilder->andWhere($filter);
            }
        }
    }
}

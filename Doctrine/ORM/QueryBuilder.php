<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\ORM;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Security\Context;

class QueryBuilder extends BaseQueryBuilder
{
    protected $queryFilters = [];
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * QueryBuilder constructor.
     *
     * @param EntityManagerInterface $em
     * @param ManagerFactory         $factory
     */
    public function __construct(EntityManagerInterface $em, ManagerFactory $factory)
    {
        parent::__construct($em);
        $this->managerFactory = $factory;
    }

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function getQuery()
    {
        if (empty($this->getDQLParts()['from'])) {
            return parent::getQuery();
        }

        $mainFrom = $this->getDQLParts()['from'][0];
        $mainAlias = $mainFrom->getAlias();
        $manager = $this->detectMainManager($mainFrom->getFrom());

        if (null !== $manager) {
            if (!empty($this->getDQLParts()['where'])) {
                $where = clone $this->getDQLParts()['where'];

                $this->resetDQLPart('where');

                $wheres[] = $where;
            } else {
                $wheres = [];
            }

            $queryFilters = $manager->getQueryFilters();
            if (0 === \count($queryFilters)) {
                throw new InvalidConfigrationException('Not found any configured filter for %s manager!', \get_class($manager));
            }

            $wheres = array_merge($wheres, $this->addFilters($mainAlias, $manager->getQueryFilters()));

            if (!empty($this->getDQLParts()['join'])) {
                foreach ($this->getDQLParts()['join'] as $joinQuery) {
                    foreach ($joinQuery as $join) {
                        $joinManager = $this->createManager($join->getJoin());
                        $joinFilters = $joinManager->getQueryFilters();

                        if (0 === \count($joinFilters)) {
                            throw new InvalidConfigrationException('Not found any configured filter for %s manager!', \get_class($joinManager));
                        }
                        $wheres = array_merge($wheres, $this->addFilters($join->getAlias(), $joinFilters));
                    }
                }
            }

            $this->andWhere($this->expr()->andX(...$wheres));
        }

        return parent::getQuery();
    }

    protected function setQueryFilters(array $queryFilters)
    {
        $this->queryFilters = $queryFilters;
    }

    protected function detectMainManager(string $entityClass): AbstractManager
    {
        $stack = debug_backtrace();

        foreach ($stack as $current) {
            if ((null !== $current['object']) && ($current['object'] instanceof AbstractManager)) {
                return $current['object'];
            }
        }

        return $this->createManager($entityClass);
    }

    protected function createManager(string $entityClass): AbstractManager
    {
        return $this->managerFactory->create($entityClass);
    }

    protected function addFilters(string $alias, array $queryFilters): array
    {
        $output = [];
        foreach ($queryFilters as $filter) {
            /** @var Comparison $x */
            $expression = $filter->getFilter($alias, $this->context);

            if ($expression) {
                $output[] = $expression;
            }
        }

        return $output;
    }
}

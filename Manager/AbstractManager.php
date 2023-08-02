<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Doctrine\ORM\Query\Expr;
use Esemve\Collection\CollectionFactory;
use Esemve\Collection\StringCollection;
use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Collections\OrderCollection;
use Nia\CoreBundle\Driver\AbstractCacheDriver;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\EntityReference;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Entity\Factory\EntityReferenceFactory;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Enum\Factory\AbstractEnumFactory;
use Nia\CoreBundle\Exception\AccessDeniedException;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Provider\AbstractLocaleProvider;
use Nia\CoreBundle\Provider\CacheProvider;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Nia\CoreBundle\Service\NiaLogger;
use Nia\CoreBundle\ValueObject\CacheItem;

abstract class AbstractManager
{
    /**
     * @var string
     */
    private $managerInsideCacheKey;

    const DEFAULT_QUERY_ALIAS = 't';
    /**
     * @var string
     */
    private $entityClass;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var EntityFactoryInterface
     */
    private $entityFactory;
    /**
     * @var EntityReferenceFactory
     */
    private $entityReferenceFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var Entity
     */
    private $entity;
    /**
     * @var AbstractEnumFactory
     */
    private $enumFactory;
    /**
     * @var ContextFactory
     */
    private $contextFactory;
    /**
     * @var array
     */
    private $queryFilters;
    /**
     * @var AbstractLocaleProvider
     */
    private $localeProvider;
    /**
     * @var CacheProvider
     */
    private $cacheProvider;
    /**
     * @var NiaLogger
     */
    private $logger;

    public function __construct(
        string $entityClass,
        array $queryFilters,
        EntityManager $entityManager,
        EntityFactoryInterface $entityFactory,
        EntityReferenceFactory $entityReferenceFactory,
        CollectionFactory $collectionFactory,
        AbstractEnumFactory $enumFactory,
        AbstractLocaleProvider $localeProvider,
        ContextFactory $contextFactory,
        CacheProvider $cacheProvider,
        NiaLogger $logger
    ) {
        $this->entityClass = $entityFactory->getEntityClassName($entityClass);
        $this->entityManager = $entityManager;
        $this->entityFactory = $entityFactory;
        $this->entityReferenceFactory = $entityReferenceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->entity = $this->initNew();
        $this->enumFactory = $enumFactory;
        $this->contextFactory = $contextFactory;

        if (empty($queryFilters)) {
            throw new InvalidConfigrationException(
                sprintf('Please set up a query filter for %s manager!', \get_class($this))
            );
        }

        foreach ($queryFilters as $filter) {
            if (false === $filter->isSupported($this->entity)) {
                throw new InvalidConfigrationException(
                    sprintf('%s filter not support %s entity!', \get_class($filter), \get_class($this->entity))
                );
            }
        }

        $this->queryFilters = $queryFilters;

        $this->configureManager();
        $this->localeProvider = $localeProvider;
        $this->cacheProvider = $cacheProvider;
        $this->logger = $logger;
    }

    protected function configureManager(): void
    {
    }

    public function getQueryFilters(): array
    {
        return $this->queryFilters;
    }

    public function getCacheTag(): string
    {
        return mb_strtolower($this->getRoleGroup());
    }

    public function getManagerCacheItem(string $key, array $plusManagerKeys, Context $context): CacheItem
    {
        if (null === $this->managerInsideCacheKey) {
            if (!empty($this->getQueryFilters())) {
                $this->managerInsideCacheKey = '';
                /** @var QueryFilterInterface $queryFilter */
                foreach ($this->getQueryFilters() as $queryFilter) {
                    $filter = $queryFilter->getFilter('t', $context);
                    $this->managerInsideCacheKey = md5($this->managerInsideCacheKey.serialize($filter));
                }
            }
        }

        return $this->getCache()->get($key.'_'.$this->managerInsideCacheKey, array_merge([$this->getCacheTag()], $plusManagerKeys));
    }

    public function getRoleGroup(): string
    {
        return 'NOTCONFIGURED';
    }

    private function initNew(): Entity
    {
        $entity = $this->entityFactory->create($this->entityClass);
        $entity->setManager($this);

        return $entity;
    }

    public function createNew(): Entity
    {
        return $this->initNew();
    }

    public function getEntityReferenceFactory(): EntityReferenceFactory
    {
        return $this->entityReferenceFactory;
    }

    public function getEntityFactory(): EntityFactoryInterface
    {
        return $this->entityFactory;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getLocaleProvider(): AbstractLocaleProvider
    {
        return $this->localeProvider;
    }

    public function getCollectionFactory(): CollectionFactory
    {
        return $this->collectionFactory;
    }

    public function getEnumFactory(): AbstractEnumFactory
    {
        return $this->enumFactory;
    }

    public function getContextFactory(): ContextFactory
    {
        return $this->contextFactory;
    }

    public function getLogger(): NiaLogger
    {
        return $this->logger;
    }

    protected function getSampleEntity(): Entity
    {
        return $this->entity;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function getManagerFactory(): ManagerFactory
    {
        return $this->entityManager->getManagerFactory();
    }

    protected function getExpr(): Expr
    {
        return $this->entityManager->getExpressionBuilder();
    }

    public function createReferenceById(int $id): EntityReference
    {
        return $this->getEntityReferenceFactory()->create($this->getEntityClass(), $id);
    }

    public function createCriteriaCollection(array $criteria): CriteriaCollection
    {
        return $this->getCollectionFactory()->create(CriteriaCollection::class, $criteria);
    }

    protected function createStringCollection(array $criteria): StringCollection
    {
        return $this->getCollectionFactory()->createStringCollection($criteria);
    }

    protected function createOrderCollection(array $criteria): OrderCollection
    {
        return $this->getCollectionFactory()->create(OrderCollection::class, $criteria);
    }

    protected function hasRight(array $votes, Entity $entity, string $event, Context $context): void
    {
        if (empty($votes)) {
            throw new AccessDeniedException();
        }

        $allow = 0;

        foreach ($votes as $filter => $vote) {
            if (null === $vote) {
                continue;
            }

            if (false === $vote) {
                throw new AccessDeniedException(sprintf('%s do not accept %s for %s entity. '."\n\n".'Roles: %s', $filter, $event, $entity->getManager()->serialize($entity), implode(',', $context->getRoles())));
            }
            ++$allow;
        }

        if ($allow > 0) {
            return;
        }

        throw new AccessDeniedException();
    }

    final public function serialize(Entity $entity): string
    {
        $reflectionClass = new \ReflectionObject($entity);
        $reflectionProperty = $reflectionClass->getProperty('manager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entity, null);

        return $this->doSerialize($entity);
    }

    protected function doSerialize(Entity $entity): string
    {
        return serialize($entity);
    }

    final public function unserialize(string $serialized): Entity
    {
        $entity = unserialize($serialized);

        return $this->afterUnserialize($entity);
    }

    protected function doUnseralize(Entity $entity): Entity
    {
        return $entity;
    }

    final public function afterUnserialize(Entity $entity): Entity
    {
        $entity->setManager($this);

        $entity = $this->doUnseralize($entity);

        if ($entity instanceof IdentifiableEntityInterface) {
            if ($entity->getId()) {
                if ($this->entityManager->getUnitOfWork()->tryGetById($entity->getId(), \get_class($entity))) {
                    $this->entityManager->getUnitOfWork()->detach($entity);
                }
                $this->entityManager->getUnitOfWork()->registerManaged($entity, ['id' => $entity->getId()], $entity->toArray());
            }
        }

        return $entity;
    }

    public function getChangeSet(Entity $entity): array
    {
        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity);
        $toArrayEntity = $entity->toArray();

        $output = [];
        foreach ($toArrayEntity as $property => $value) {
            if (isset($originalData[$property])) {
                if (serialize($value) !== serialize($originalData[$property])) {
                    $output[$property] = ['original_value' => $originalData[$property], 'new_value' => $value];
                }
            }
        }

        return $output;
    }

    public function getCache(): AbstractCacheDriver
    {
        return $this->cacheProvider->provide();
    }
}

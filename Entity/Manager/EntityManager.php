<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Manager;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager as BaseEntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PessimisticLockException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Nia\CoreBundle\Doctrine\ORM\Filter\AbstractFilter;
use Nia\CoreBundle\Entity\Factory\EntityFactory;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Repository\AbstractRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityManager extends EntityManagerDecorator
{
    /**
     * @var BaseEntityManager
     */
    private $entityManager;
    /**
     * @var ManagerFactory
     */
    private $managerFactory;
    /**
     * @var EntityFactory
     */
    private $entityFactory;

    public function __construct(BaseEntityManager $entityManager, EntityFactory $entityFactory, ManagerFactory $managerFactory, array $disabledFilters, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->managerFactory = $managerFactory;
        $this->entityFactory = $entityFactory;

        $this->injectToFilters($disabledFilters, $container);

        parent::__construct($entityManager);
    }

    /**
     * Returns the cache API for managing the second level cache regions or NULL if the cache is not enabled.
     *
     * @return \Doctrine\ORM\Cache|null
     */
    public function getCache()
    {
        return $this->entityManager->getCache();
    }

    /**
     * Gets the database connection object used by the EntityManager.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->entityManager->getConnection();
    }

    /**
     * Gets an ExpressionBuilder used for object-oriented construction of query expressions.
     *
     * Example:
     *
     * <code>
     *     $qb = $em->createQueryBuilder();
     *     $expr = $em->getExpressionBuilder();
     *     $qb->select('u')->from('User', 'u')
     *         ->where($expr->orX($expr->eq('u.id', 1), $expr->eq('u.id', 2)));
     * </code>
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    public function getExpressionBuilder()
    {
        return $this->entityManager->getExpressionBuilder();
    }

    /**
     * Starts a transaction on the underlying database connection.
     */
    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * Executes a function in a transaction.
     *
     * The function gets passed this EntityManager instance as an (optional) parameter.
     *
     * {@link flush} is invoked prior to transaction commit.
     *
     * If an exception occurs during execution of the function or flushing or transaction commit,
     * the transaction is rolled back, the EntityManager closed and the exception re-thrown.
     *
     * @param callable $func the function to execute transactionally
     *
     * @return mixed the non-empty value returned from the closure or true instead
     */
    public function transactional($func)
    {
        return $this->entityManager->transactional($func);
    }

    /**
     * Commits a transaction on the underlying database connection.
     */
    public function commit()
    {
        $this->entityManager->commit();
    }

    /**
     * Performs a rollback on the underlying database connection.
     */
    public function rollback()
    {
        $this->entityManager->rollback();
    }

    /**
     * Creates a new Query object.
     *
     * @param string $dql the DQL string
     *
     * @return Query
     */
    public function createQuery($dql = '')
    {
        return $this->entityManager->createQuery($dql);
    }

    /**
     * Creates a Query from a named query.
     *
     * @param string $name
     *
     * @return Query
     */
    public function createNamedQuery($name)
    {
        return $this->createNamedQuery($name);
    }

    /**
     * Creates a native SQL query.
     *
     * @param string           $sql
     * @param ResultSetMapping $rsm the ResultSetMapping to use
     *
     * @return NativeQuery
     */
    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        return $this->entityManager->createNativeQuery($sql, $rsm);
    }

    /**
     * Creates a NativeQuery from a named native query.
     *
     * @param string $name
     *
     * @return NativeQuery
     */
    public function createNamedNativeQuery($name)
    {
        return $this->entityManager->createNamedNativeQuery($name);
    }

    /**
     * Create a QueryBuilder instance.
     *
     * @return \Nia\CoreBundle\Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new \Nia\CoreBundle\Doctrine\ORM\QueryBuilder($this, $this->managerFactory);
    }

    /**
     * Create a QueryBuilder instance.
     *
     * @return QueryBuilder
     */
    public function createUnconfiguredQueryBuilder()
    {
        return $this->entityManager->createQueryBuilder();
    }

    /**
     * Gets a reference to the entity identified by the given type and identifier
     * without actually loading it, if the entity is not yet loaded.
     *
     * @param string $entityName the name of the entity type
     * @param mixed  $id         the entity identifier
     *
     * @throws ORMException
     *
     * @return object|null the entity reference
     */
    public function getReference($entityName, $id)
    {
        return $this->entityManager->getReference($entityName, $id);
    }

    /**
     * Gets a partial reference to the entity identified by the given type and identifier
     * without actually loading it, if the entity is not yet loaded.
     *
     * The returned reference may be a partial object if the entity is not yet loaded/managed.
     * If it is a partial object it will not initialize the rest of the entity state on access.
     * Thus you can only ever safely access the identifier of an entity obtained through
     * this method.
     *
     * The use-cases for partial references involve maintaining bidirectional associations
     * without loading one side of the association or to update an entity without loading it.
     * Note, however, that in the latter case the original (persistent) entity data will
     * never be visible to the application (especially not event listeners) as it will
     * never be loaded in the first place.
     *
     * @param string $entityName the name of the entity type
     * @param mixed  $identifier the entity identifier
     *
     * @return object the (partial) entity reference
     */
    public function getPartialReference($entityName, $identifier)
    {
        return $this->entityManager->getPartialReference($entityName, $identifier);
    }

    /**
     * Closes the EntityManager. All entities that are currently managed
     * by this EntityManager become detached. The EntityManager may no longer
     * be used after it is closed.
     */
    public function close()
    {
        $this->entityManager->close();
    }

    /**
     * Creates a copy of the given entity. Can create a shallow or a deep copy.
     *
     * @param object $entity the entity to copy
     * @param bool   $deep   FALSE for a shallow copy, TRUE for a deep copy
     *
     * @throws \BadMethodCallException
     *
     * @return object the new entity
     */
    public function copy($entity, $deep = false)
    {
        return $this->entityManager->copy($entity, $deep);
    }

    /**
     * Acquire a lock on the given entity.
     *
     * @param object   $entity
     * @param int      $lockMode
     * @param int|null $lockVersion
     *
     * @throws OptimisticLockException
     * @throws PessimisticLockException
     */
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->entityManager->lock($entity, $lockMode, $lockVersion);
    }

    /**
     * Gets the EventManager used by the EntityManager.
     *
     * @return \Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->entityManager->getEventManager();
    }

    /**
     * Gets the Configuration used by the EntityManager.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->entityManager->getConfiguration();
    }

    /**
     * Check if the Entity manager is open or closed.
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->entityManager->isOpen();
    }

    /**
     * Gets the UnitOfWork used by the EntityManager to coordinate operations.
     *
     * @return UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->entityManager->getUnitOfWork();
    }

    /**
     * Gets a hydrator for the given hydration mode.
     *
     * This method caches the hydrator instances which is used for all queries that don't
     * selectively iterate over the result.
     *
     * @deprecated
     *
     * @param int $hydrationMode
     *
     * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
     */
    public function getHydrator($hydrationMode)
    {
        return $this->entityManager->getHydrator($hydrationMode);
    }

    /**
     * Create a new instance for the given hydration mode.
     *
     * @param int $hydrationMode
     *
     * @throws ORMException
     *
     * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
     */
    public function newHydrator($hydrationMode)
    {
        return $this->entityManager->newHydrator($hydrationMode);
    }

    /**
     * Gets the proxy factory used by the EntityManager to create entity proxies.
     *
     * @return \Doctrine\ORM\Proxy\ProxyFactory
     */
    public function getProxyFactory()
    {
        return $this->entityManager->getProxyFactory();
    }

    /**
     * Gets the enabled filters.
     *
     * @return \Doctrine\ORM\Query\FilterCollection the active filter collection
     */
    public function getFilters()
    {
        return $this->entityManager->getFilters();
    }

    /**
     * Checks whether the state of the filter collection is clean.
     *
     * @return bool true, if the filter collection is clean
     */
    public function isFiltersStateClean()
    {
        return $this->entityManager->isFiltersStateClean();
    }

    /**
     * Checks whether the Entity Manager has filters.
     *
     * @return bool true, if the EM has a filter collection
     */
    public function hasFilters()
    {
        return $this->entityManager->hasFilters();
    }

    public function find($entityName, $id, $lockMode = null, $lockVersion = null)
    {
        return $this->entityManager->find($entityName, $id, $lockMode, $lockVersion);
    }

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object the instance to make managed and persistent
     */
    public function persist($object)
    {
        $this->entityManager->persist($object);
    }

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object the object instance to remove
     */
    public function remove($object)
    {
        $this->entityManager->remove($object);
    }

    /**
     * Merges the state of a detached object into the persistence context
     * of this ObjectManager and returns the managed copy of the object.
     * The object passed to merge will not become associated/managed with this ObjectManager.
     *
     * @param object $object
     *
     * @return object
     */
    public function merge($object)
    {
        $this->entityManager->merge($object);
    }

    /**
     * Clears the ObjectManager. All objects that are currently managed
     * by this ObjectManager become detached.
     *
     * @param string|null $objectName if given, only objects of this type will get detached
     */
    public function clear($objectName = null)
    {
        $this->entityManager->clear($objectName);
    }

    /**
     * Detaches an object from the ObjectManager, causing a managed object to
     * become detached. Unflushed changes made to the object if any
     * (including removal of the object), will not be synchronized to the database.
     * Objects which previously referenced the detached object will continue to
     * reference it.
     *
     * @param object $object the object to detach
     */
    public function detach($object)
    {
        $this->entityManager->detach($object);
    }

    /**
     * Refreshes the persistent state of an object from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $object the object to refresh
     */
    public function refresh($object)
    {
        $this->entityManager->refresh($object);
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     */
    public function flush($entity = null)
    {
        return $this->entityManager->flush($entity);
    }

    /**
     * Gets the repository for a class.
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        $repository = $this->entityManager->getRepository(
            $this->entityFactory->getEntityClassName($className)
        );

        if ($repository instanceof AbstractRepository) {
            $repository->setManager($this);
        }

        return $repository;
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->entityManager->getMetadataFactory();
    }

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     *
     * @param object $obj
     */
    public function initializeObject($obj)
    {
        $this->entityManager->initializeObject($obj);
    }

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     *
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        return $this->entityManager->contains($object);
    }

    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->entityManager, $name], $arguments);
    }

    public function getClassMetadata($className)
    {
        return $this->entityManager->getClassMetadata($className);
    }

    private function injectToFilters(array $disabledFilters, ContainerInterface $container): void
    {
        foreach ($this->entityManager->getFilters()->getEnabledFilters() as $filter) {
            if ($filter instanceof AbstractFilter) {
                $filter->setContainer($container);
                $filter->setDisabledFilters($disabledFilters);
            }
        }
    }

    public function getManagerFactory(): ManagerFactory
    {
        return $this->managerFactory;
    }
}

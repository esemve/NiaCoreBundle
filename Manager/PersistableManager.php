<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Entity\LoggedEntityInterface;
use Nia\CoreBundle\Entity\PositionableEntity;
use Nia\CoreBundle\Entity\SoftDeleteEntityInterface;
use Nia\CoreBundle\Enum\IsNewEnum;
use Nia\CoreBundle\Event\ManagerEvent;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Nia\CoreBundle\Exception\InvalidParameterException;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\QueryFilters\AbstractQueryFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PersistableManager extends ReadOnlyManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function save(Entity $entity, Context $context): void
    {
        $this->doSave($entity, $context);
    }

    private function persist(Entity $entity, Context $context): void
    {
        $event = 'create';

        foreach ($this->getQueryFilters() as $filter) {
            if (method_exists($entity, 'getId')) {
                if ($entity->getId()) {
                    $event = 'edit';
                    $filterAnswers[\get_class($filter)] = $filter->canEdit($entity, $context);
                } else {
                    $filterAnswers[\get_class($filter)] = $filter->canCreate($entity, $context);
                }
            } else {
                $filterAnswers[\get_class($filter)] = $filter->canCreate($entity, $context);
            }
        }

        $this->hasRight($filterAnswers, $entity, $event, $context);

        $this->checkEntityType($entity);

        $this->getEntityManager()->persist($entity);
    }

    public function saveList(EntityCollection $entities, Context $context): void
    {
        $this->beginTransaction();

        try {
            foreach ($entities as $entity) {
                $this->save($entity, $context);
            }

            $this->commit();
        } catch (\Exception $ex) {
            $this->rollBack();
        }

        $this->flush();
    }

    private function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(Entity $entity, Context $context, bool $force = false): void
    {
        $this->doRemove($entity, $context, $force);
    }

    public function removeList(EntityCollection $entities, Context $context, bool $force = false): void
    {
        $this->beginTransaction();
        try {
            foreach ($entities as $entity) {
                $this->remove($entity, $force);
            }

            $this->commit();
        } catch (\Exception $ex) {
            $this->rollBack();
        }

        $this->flush();
    }

    public function beginTransaction(): void
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getEntityManager()->commit();
    }

    public function rollBack(): void
    {
        $this->getEntityManager()->rollback();
    }

    protected function checkEntityType(Entity $entity): void
    {
        if (is_subclass_of($entity, $this->getEntityClass())) {
            throw new InvalidParameterException('Not supported %s entity in this manager!', \get_class($entity));
        }
    }

    final protected function doSave(Entity $entity, Context $context): void
    {
        if (method_exists($entity, 'getId')) {
            if (null === $entity->getId()) {
                if ($this instanceof PositionableManagerInterface) {
                    if ($entity instanceof PositionableEntity) {
                        $entity->setPosition($this->getNextPosition($this->getPositionCriteria($entity, $context), $context));
                    } else {
                        throw new InvalidConfigrationException(sprintf('%s manager use PositionableManagerInterface but the entity not!', \get_called_class()));
                    }
                }

                if ($entity instanceof LoggedEntityInterface) {
                    $entity->setCreatedBy(
                        $this->getEntityReferenceFactory()->createByEntity(
                            $context->getUser()
                        )
                    );
                }
            }
        }

        if ($entity instanceof LoggedEntityInterface) {
            $entity->setUpdatedBy(
                $this->getEntityReferenceFactory()->createByEntity(
                    $context->getUser()
                )
            );
        }

        if (method_exists($entity, 'getId')) {
            if ($entity->getId()) {
                $isNew = $this->getEnumFactory()->create(IsNewEnum::class, IsNewEnum::FALSE);
            } else {
                $isNew = $this->getEnumFactory()->create(IsNewEnum::class, IsNewEnum::TRUE);
            }
        } else {
            $isNew = $this->getEnumFactory()->create(IsNewEnum::class, IsNewEnum::UNKNOWN);
        }

        $this->beginTransaction();

        try {
            $this->dispatch(ManagerEvent::BEFORE_SAVE, $entity, $context, $isNew);

            $this->persist($entity, $context);

            $this->flush();

            $this->dispatch(ManagerEvent::AFTER_SAVE, $entity, $context, $isNew);

            if ($entity instanceof IdentifiableEntityInterface) {
                if ($isNew) {
                    $this->getLogger()->logCreate($entity->getEntityId());
                } else {
                    $this->getLogger()->logEdit($entity->getEntityId());
                }
            }

            $this->commit();
        } catch (\Exception $ex) {
            $this->rollBack();

            $this->dispatch(ManagerEvent::ERROR_SAVE, $entity, $context, $isNew);

            throw $ex;
        }

        $this->dispatch(ManagerEvent::SUCCESS_SAVE, $entity, $context, $isNew);
        $this->getCache()->clearByTag($this->getCacheTag());
        $this->postSuccessSave($entity, $context);
    }

    protected function doRemove(Entity $entity, Context $context, bool $force = false): void
    {
        $this->checkEntityType($entity);

        $filterAnswers = [];
        foreach ($this->getQueryFilters() as $filter) {
            /* @var AbstractQueryFilter $filter */
            $filterAnswers[\get_class($filter)] = $filter->canDelete($entity, $context);
        }

        $this->hasRight($filterAnswers, $entity, 'remove', $context);

        if ((false === $force) && ($entity instanceof SoftDeleteEntityInterface)) {
            $entity->setDeleted();
            $this->beginTransaction();

            try {
                $this->dispatch(ManagerEvent::BEFORE_REMOVE, $entity, $context);
                $this->dispatch(ManagerEvent::BEFORE_SOFTDELETE, $entity, $context);

                $this->getEntityManager()->persist($entity);

                $this->flush();

                $this->dispatch(ManagerEvent::AFTER_SOFTDELETE, $entity, $context);
                $this->dispatch(ManagerEvent::AFTER_REMOVE, $entity, $context);

                if ($entity instanceof IdentifiableEntityInterface) {
                    $this->getLogger()->logDelete($entity->getEntityId());
                }

                $this->commit();
            } catch (\Exception $ex) {
                $this->rollBack();

                $this->dispatch(ManagerEvent::ERROR_SOFTDELETE, $entity, $context);
                $this->dispatch(ManagerEvent::ERROR_REMOVE, $entity, $context);

                throw $ex;
            }

            $this->dispatch(ManagerEvent::SUCCESS_SOFTDELETE, $entity, $context);
            $this->dispatch(ManagerEvent::SUCCESS_REMOVE, $entity, $context);

            return;
        }

        $this->beginTransaction();

        if (true === $force) {
            try {
                $this->dispatch(ManagerEvent::BEFORE_REMOVE, $entity, $context);
                $this->dispatch(ManagerEvent::BEFORE_FORCE_REMOVE, $entity, $context);

                $this->getEntityManager()->remove($entity);

                $this->flush();

                $this->dispatch(ManagerEvent::AFTER_FORCE_REMOVE, $entity, $context);
                $this->dispatch(ManagerEvent::AFTER_REMOVE, $entity, $context);

                if ($entity instanceof IdentifiableEntityInterface) {
                    $this->getLogger()->logDelete($entity->getEntityId());
                }

                $this->commit();
            } catch (\Exception $ex) {
                $this->rollBack();

                $this->dispatch(ManagerEvent::ERROR_FORCE_REMOVE, $entity, $context);
                $this->dispatch(ManagerEvent::ERROR_REMOVE, $entity, $context);

                throw $ex;
            }

            $this->dispatch(ManagerEvent::SUCCESS_FORCE_REMOVE, $entity, $context);
            $this->dispatch(ManagerEvent::SUCCESS_REMOVE, $entity, $context);

            return;
        }

        try {
            $this->dispatch(ManagerEvent::BEFORE_REMOVE, $entity, $context);

            $this->getEntityManager()->remove($entity);

            $this->flush();

            $this->dispatch(ManagerEvent::AFTER_REMOVE, $entity, $context);

            if ($entity instanceof IdentifiableEntityInterface) {
                $this->getLogger()->logDelete($entity->getEntityId());
            }

            $this->commit();
        } catch (\Exception $ex) {
            $this->rollBack();

            $this->dispatch(ManagerEvent::ERROR_REMOVE, $entity, $context);

            throw $ex;
        }

        $this->dispatch(ManagerEvent::SUCCESS_REMOVE, $entity, $context);
    }

    public function dispatch(string $eventName, Entity $entity, Context $context, ?IsNewEnum $isNewEnum = null): void
    {
        $name = explode('\\', \get_class($entity));
        $name = end($name);

        $this->getEventDispatcher()->dispatch($eventName, new ManagerEvent($entity, $isNewEnum, $context));
        $this->getEventDispatcher()->dispatch($eventName.'.'.$name, new ManagerEvent($entity, $isNewEnum, $context));
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function postSuccessSave(Entity $entity, Context $context): void
    {
    }
}

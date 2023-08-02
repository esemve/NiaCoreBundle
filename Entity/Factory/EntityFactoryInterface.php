<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Factory;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Security\Context;

interface EntityFactoryInterface
{
    /**
     * Create an empty entity.
     *
     * @param string $class
     *
     * @return Entity
     */
    public function create(string $class): Entity;

    /**
     * Create an empty entity with manager.
     *
     * @param string $class
     *
     * @return Entity
     */
    public function createEntityWithManager(string $class): Entity;

    /**
     * Get overrided entity class name if overrided the entity.
     *
     * @param string $className
     *
     * @return string
     */
    public function getEntityClassName(string $className): string;

    /**
     * Inject a manager to an entity.
     *
     * @return mixed
     */
    public function injectManager(Entity $entity): Entity;

    /**
     * Create entity by entityId.
     *
     * @param string $entityId
     *
     * @return IdentifiableEntityInterface
     */
    public function createByEntityId(string $entityId, Context $context): IdentifiableEntityInterface;

    public function getManagerFactory(): ManagerFactory;
}

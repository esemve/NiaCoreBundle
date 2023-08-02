<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Factory;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Exception\InvalidParameterException;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Security\Context;

class EntityFactory implements EntityFactoryInterface
{
    /**
     * @var array
     */
    private $overrideMap = [];
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    public function __construct(array $overrideMap, ManagerFactory $managerFactory)
    {
        $this->overrideMap = $overrideMap;
        $this->managerFactory = $managerFactory;
    }

    public function getEntityClassName(string $className): string
    {
        if (isset($this->overrideMap[$className])) {
            $className = $this->overrideMap[$className];
        }

        return $className;
    }

    public function createByEntityId(string $entityId, Context $context): IdentifiableEntityInterface
    {
        $entityId = explode(':', $entityId);
        $class = $entityId[0];
        $id = $entityId[1];

        $manager = $this->managerFactory->create($class);

        return $manager->findById((int) $id, $context);
    }

    public function create(string $class): Entity
    {
        $class = $this->getEntityClassName($class);
        $entity = new $class();

        if (!$entity instanceof Entity) {
            throw new InvalidParameterException();
        }

        return $entity;
    }

    public function createEntityWithManager(string $class): Entity
    {
        $class = $this->getEntityClassName($class);
        $entity = new $class();
        $this->injectManager($entity);

        if (!$entity instanceof Entity) {
            throw new InvalidParameterException();
        }

        return $entity;
    }

    /**
     * Inject a manager to an entity.
     *
     * @return mixed
     */
    public function injectManager(Entity $entity): Entity
    {
        $manager = $this->managerFactory->create(
            \get_class($entity)
        );
        $entity->setManager($manager);

        return $entity;
    }

    /**
     * @return ManagerFactory
     */
    public function getManagerFactory(): ManagerFactory
    {
        return $this->managerFactory;
    }
}

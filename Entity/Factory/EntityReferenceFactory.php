<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Factory;

use Esemve\Collection\IntegerCollection;
use Nia\CoreBundle\Collections\EntityReferenceCollection;
use Nia\CoreBundle\Entity\EntityReference;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;

class EntityReferenceFactory implements EntityReferenceFactoryInterface
{
    /**
     * @var EntityFactoryInterface
     */
    private $entityFactory;
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    public function __construct(ManagerFactory $managerFactory, EntityFactoryInterface $entityFactory)
    {
        $this->entityFactory = $entityFactory;
        $this->managerFactory = $managerFactory;
    }

    public function create(string $class, int $id): EntityReference
    {
        return new EntityReference(
            $this->managerFactory->create($class),
            $this->entityFactory->getEntityClassName($class),
            $id
        );
    }

    public function createByEntity(IdentifiableEntityInterface $entity): EntityReference
    {
        $class = \get_class($entity);

        return $this->create($class, $entity->getId());
    }

    public function createByIds(string $class, IntegerCollection $ids): EntityReferenceCollection
    {
        $output = [];

        foreach ($ids as $id) {
            $output[$id] = $this->create($class, $id);
        }

        return new EntityReferenceCollection($output);
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Nia\CoreBundle\Manager\AbstractManager;
use Nia\CoreBundle\Manager\ReadOnlyManager;

class EntityReference
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var IdentifiableEntityInterface
     */
    protected $entityCache;

    /**
     * @var ReadOnlyManager
     */
    private $manager;

    public function __construct(AbstractManager $manager, string $class, int $id)
    {
        $this->class = $class;
        $this->id = $id;
        $this->manager = $manager;
    }

    public function getEntityClass(): string
    {
        return $this->class;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEntity(): ?IdentifiableEntityInterface
    {
        return $this->manager->findById($this->getId());
    }
}

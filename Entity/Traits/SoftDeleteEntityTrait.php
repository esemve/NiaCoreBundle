<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Entity\Entity;

trait SoftDeleteEntityTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deleted_at;

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deleted_at;
    }

    public function isDeleted(): bool
    {
        if (null === $this->deleted_at) {
            return false;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function setDeleted(): Entity
    {
        $this->deleted_at = new \DateTime('now');

        return $this;
    }
}

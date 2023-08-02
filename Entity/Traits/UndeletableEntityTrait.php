<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

trait UndeletableEntityTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(name="undeletable", type="boolean", nullable=true)
     */
    protected $undeletable;

    public function isUndeletable(): bool
    {
        if (true === $this->undeletable) {
            return true;
        }

        return false;
    }

    public function setUndeletable(bool $undeletable): void
    {
        $this->undeletable = $undeletable;
    }
}

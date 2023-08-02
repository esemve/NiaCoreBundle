<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Entity\Entity;

trait PositionableEntityTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    public function setPosition(int $position): Entity
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
}

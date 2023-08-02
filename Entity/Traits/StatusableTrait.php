<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Enum\StatusEnum;

trait StatusableTrait
{
    /**
     * @var StatusEnum|null
     *
     * @ORM\Column(name="status", type="statusEnum")
     */
    protected $status;

    public function getStatus(): ?StatusEnum
    {
        return $this->status;
    }

    public function setStatus(StatusEnum $statusEnum): Entity
    {
        $this->status = $statusEnum;

        return $this;
    }
}

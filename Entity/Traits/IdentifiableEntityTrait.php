<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

trait IdentifiableEntityTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isNew(): bool
    {
        if (!$this->getId()) {
            return true;
        }

        return false;
    }

    public function getEntityId(): string
    {
        return \get_class($this).':'.$this->id;
    }
}

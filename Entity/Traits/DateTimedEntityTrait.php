<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

trait DateTimedEntityTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"} )
     *
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"} )
     *
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * @ORM\PrePersist
     */
    public function doPrePersist()
    {
        $this->created_at = new \DateTime('now');
        $this->updated_at = new \DateTime('now');
    }

    /**
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updated_at = new \DateTime('now');
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }
}

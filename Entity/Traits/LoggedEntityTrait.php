<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\EntityReference;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\UserBundle\Entity\User;

trait LoggedEntityTrait
{
    /**
     * @var AbstractManager
     */
    protected $manager;

    /**
     * @var int
     *
     * @ORM\Column(name="created_by", type="integer", nullable=true)
     */
    protected $created_by;

    /**
     * @var int
     *
     * @ORM\Column(name="updated_by", type="integer", nullable=true)
     */
    protected $updated_by;

    /**
     * @param EntityReference $reference
     *
     * @return $this
     */
    public function setCreatedBy(?EntityReference $reference = null): Entity
    {
        if (null !== $reference) {
            $this->created_by = $reference->getId();
        }

        return $this;
    }

    /**
     * @param EntityReference $reference
     *
     * @return $this
     */
    public function setUpdatedBy(?EntityReference $reference = null): Entity
    {
        if (null !== $reference) {
            $this->updated_by = $reference->getId();
        }

        return $this;
    }

    public function getCreatedBy(): EntityReference
    {
        return $this->manager->getEntityReferenceFactory()->create(User::class, $this->created_by);
    }

    public function getUpdatedBy(): EntityReference
    {
        return $this->manager->getEntityReferenceFactory()->create(User::class, $this->updated_by);
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Entity\Entity;

trait PublishableTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="public_from", type="datetime", nullable=true)
     */
    protected $public_from;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="public_to", type="datetime", nullable=true)
     */
    protected $public_to;

    public function setPublicFrom(?\DateTime $fromDate = null): Entity
    {
        $this->public_from = $fromDate;
    }

    public function setPublicTo(?\DateTime $toDate = null): Entity
    {
        $this->public_to = $toDate;
    }

    public function getPublicFrom(): ?\DateTime
    {
        return $this->public_from;
    }

    public function getPublicTo(): ?\DateTime
    {
        return $this->public_to;
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface SoftDeleteEntityInterface
{
    public function getDeletedAt(): ?\DateTime;

    public function isDeleted(): bool;

    public function setDeleted(): Entity;
}

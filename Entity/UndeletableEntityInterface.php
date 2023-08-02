<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface UndeletableEntityInterface
{
    public function isUndeletable(): bool;

    public function setUndeletable(bool $undeletable): void;
}

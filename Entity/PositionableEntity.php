<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface PositionableEntity
{
    public function setPosition(int $position): Entity;

    public function getPosition(): ?int;
}

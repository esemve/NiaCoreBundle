<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface IdentifiableEntityInterface
{
    public function getId(): ?int;

    public function getEntityId(): string;
}

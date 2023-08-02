<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Nia\CoreBundle\Enum\StatusEnum;

interface StatusableInterace
{
    public function getStatus(): ?StatusEnum;

    public function setStatus(StatusEnum $statusEnum): Entity;
}

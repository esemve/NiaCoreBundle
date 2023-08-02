<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface DateTimedEntityInterface
{
    public function getCreatedAt(): \DateTime;

    public function getUpdatedAt(): \DateTime;
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Nia\CoreBundle\Manager\AbstractManager;

interface Entity
{
    public function setManager(AbstractManager $manager): void;

    public function getManager(): AbstractManager;

    public function toArray(): array;
}

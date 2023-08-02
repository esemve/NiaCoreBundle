<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

class LogManager extends PersistableManager
{
    public function getRoleGroup(): string
    {
        return 'LOG';
    }
}

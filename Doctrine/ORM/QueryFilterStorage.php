<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\ORM;

use Nia\CoreBundle\Exception\InvalidConfigrationException;

class QueryFilterStorage
{
    protected static $storage;

    public static function setFilters(string $manager, array $filters): void
    {
        self::$storage[$manager] = $filters;
    }

    public static function getFilters(string $manager): array
    {
        if (empty(self::$storage[$manager])) {
            throw new InvalidConfigrationException(sprintf('Not found any query filter for %s!', $manager));
        }

        return self::$storage[$manager] ?? [];
    }
}

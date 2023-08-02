<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Nia\CoreBundle\ValueObject\CacheItem;

class VoidCacheDriver extends AbstractCacheDriver
{
    public function get(string $key, array $tags = []): CacheItem
    {
        $this->createEmptyItem($this->generateKey($key, $tags));

        return $this->createEmptyItem($this->generateKey($key, $tags));
    }

    public function set(CacheItem $item): bool
    {
        return true;
    }

    public function getItems(array $itemIds, array $tags = []): array
    {
        return [];
    }

    public function getByFilter(string $filter): array
    {
        return [];
    }

    public function clearByFilter(string $filter): void
    {
    }

    public function clearByTag(string $tag): void
    {
    }
}

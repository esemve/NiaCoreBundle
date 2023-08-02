<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Nia\CoreBundle\ValueObject\CacheItem;

abstract class AbstractCacheDriver
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    abstract public function get(string $key, array $tags = []): CacheItem;

    abstract public function set(CacheItem $item): bool;

    abstract public function getItems(array $itemIds): array;

    abstract public function getByFilter(string $filter): array;

    abstract public function clearByFilter(string $filter): void;

    abstract public function clearByTag(string $tag): void;

    protected function generateKey(string $key, array $tags = []): string
    {
        $tags = implode(':', $tags);

        return $this->prefix.':'.($tags ? $tags.':' : '').$key;
    }

    protected function createEmptyItem(string $key): CacheItem
    {
        return new CacheItem($key, null, false);
    }

    protected function createItem(string $key, $value, $isHit = true): CacheItem
    {
        return new CacheItem($key, $value, $isHit);
    }
}

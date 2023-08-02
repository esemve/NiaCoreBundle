<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Nia\CoreBundle\ValueObject\CacheItem;

class ArrayCacheDriver extends AbstractCacheDriver
{
    private $cache = [];

    public function get(string $key, array $tags = []): CacheItem
    {
        if (empty($this->cache[$this->generateKey($key, $tags)])) {
            $this->cache[$this->generateKey($key, $tags)] = $this->createEmptyItem($this->generateKey($key, $tags));
        }

        return $this->cache[$this->generateKey($key, $tags)];
    }

    public function set(CacheItem $item): bool
    {
        $this->cache[$item->getKey()] = $item;

        return true;
    }

    public function getItems(array $itemIds, array $tags = []): array
    {
        $output = [];

        foreach ($itemIds as $key => $id) {
            $output[$id] = $this->get($id, $tags);
        }

        return $output;
    }

    public function getByFilter(string $filter): array
    {
        $filter = str_replace('*', '.*', $filter);

        $output = [];

        foreach ($this->cache as $key => $value) {
            preg_match('/^'.$filter.'$/', $key, $matches);
            if (\count($matches) > 0) {
                $output[] = $value;
            }
        }

        return $output;
    }

    public function clearByFilter(string $filter): void
    {
        $filter = str_replace('*', '.*', $filter);

        foreach ($this->cache as $key => $value) {
            preg_match('/^'.$filter.'$/', $key, $matches);
            if (\count($matches) > 0) {
                unset($this->cache[$key]);
            }
        }
    }

    public function clearByTag(string $tag): void
    {
        $this->clearByFilter('*:'.$tag.':*');
    }
}

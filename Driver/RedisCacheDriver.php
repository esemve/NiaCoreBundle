<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Esemve\Collection\AbstractTypedCollection;
use Nia\CoreBundle\DataCollector\CacheDataCollector;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\ValueObject\CacheItem;
use Predis\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisCacheDriver extends AbstractCacheDriver
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @var CacheDataCollector
     */
    private $dataCollector;

    public function __construct($prefix, $connection, ContainerInterface $container)
    {
        parent::__construct($prefix);
        $this->client = new Client($connection);
        $this->managerFactory = $container->get('nia.core.manager.factory');
        $this->dataCollector = $container->get('nia.core.cache.data_collector');
    }

    public function get(string $key, array $tags = []): CacheItem
    {
        $cacheKey = $this->generateKey($key, $tags);

        $value = $this->client->get($cacheKey);
        $isHit = (null !== $value);

        $this->dataCollector->add($cacheKey, $key, $isHit, $tags);

        return $this->createItem($this->generateKey($key, $tags), $this->reverseTransform($value), $isHit);
    }

    public function set(CacheItem $item): bool
    {
        return (bool) $this->client->setex($item->getKey(), $item->getTtl(), $this->transform($item->get()));
    }

    public function getItems(array $itemIds, array $tags = []): array
    {
        $keys = [];
        foreach ($itemIds as $key) {
            $keys[] = $this->generateKey($key, $tags);
        }

        $output = [];

        foreach ($this->client->mget($keys) ?? [] as $key => $value) {
            $output[$key] = $this->createItem($keys[$key], $this->reverseTransform($value), null !== $value);
        }

        return $output;
    }

    public function getByFilter(string $filter): array
    {
        $keys = $this->client->keys($this->generateKey($filter, []));

        $output = [];

        foreach ($this->client->mget($keys) ?? [] as $key => $value) {
            $output[$key] = $this->createItem($keys[$key], $this->reverseTransform($value), null !== $value);
        }

        return $output;
    }

    public function clearByFilter(string $filter): void
    {
        $keys = $this->client->keys($this->generateKey($filter, []));
        if (!empty($keys)) {
            $this->client->del($keys);
        }
    }

    public function clearByTag(string $tag): void
    {
        $this->clearByFilter('*:'.$tag.':*');
        $this->clearByFilter($tag.':*');
    }

    private function transform($value): string
    {
        if (\is_array($value)) {
            foreach ($value as &$elem) {
                if (\is_object($elem)) {
                    if ($elem instanceof Entity) {
                        $elem = unserialize($elem->getManager()->serialize($elem));
                    }
                }
            }
        }

        if (\is_object($value)) {
            if ($value instanceof Entity) {
                return $value->getManager()->serialize(clone $value);
            }

            if ($value instanceof AbstractTypedCollection) {
                foreach ($value as &$elem) {
                    if (\is_object($elem)) {
                        if ($elem instanceof Entity) {
                            $elem = clone $elem;
                            $elem = unserialize($elem->getManager()->serialize($elem));
                        }
                    }
                }
            }
        }

        return serialize($value);
    }

    private function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        $object = unserialize($value);

        if (\is_object($object)) {
            if ($object instanceof Entity) {
                return $this->managerFactory->createByEntity($object)->afterUnserialize($object);
            }
        }
        if (is_iterable($object)) {
            foreach ($object as $key => &$value) {
                if (\is_object($value)) {
                    if ($value instanceof Entity) {
                        $this->managerFactory->createByEntity($value)->afterUnserialize($value);
                    }
                }
            }
        }

        return $object;
    }
}

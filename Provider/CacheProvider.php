<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Provider;

use Nia\CoreBundle\Driver\AbstractCacheDriver;
use Nia\CoreBundle\Driver\ArrayCacheDriver;
use Nia\CoreBundle\Driver\RedisCacheDriver;
use Nia\CoreBundle\Driver\VoidCacheDriver;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Symfony\Component\DependencyInjection\Container;

class CacheProvider
{
    /**
     * @var AbstractCacheDriver
     */
    protected $driver;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $connection;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var Container
     */
    private $container;

    public function __construct(
        string $type,
        string $connection,
        string $prefix,
        Container $container
    ) {
        $this->type = $type;
        $this->connection = $connection;
        $this->prefix = $prefix;
        $this->container = $container;
    }

    public function provide(): AbstractCacheDriver
    {
        if (empty($this->driver)) {
            $this->initDriver();
        }

        return $this->driver;
    }

    private function initDriver(): void
    {
        if ('test' === $this->container->getParameter('kernel.environment')) {
            $this->type = 'array';
        }

        if (isset($_ENV['FORCE_ARRAY_CACHE'])) {
            $this->type = 'array';
        }

        switch ($this->type) {
            case 'array':
                $this->initArrayDriver();

                return;
            case 'redis':
                $this->initRedisDriver();

                return;
            case 'void':
                $this->initVoidDriver();

                return;
        }
        throw new InvalidConfigrationException('Not found valid cache driver! %s', $this->type);
    }

    private function initArrayDriver(): void
    {
        $this->driver = new ArrayCacheDriver($this->prefix);
    }

    private function initRedisDriver(): void
    {
        $this->driver = new RedisCacheDriver($this->prefix, $this->connection, $this->container);
    }

    private function initVoidDriver(): void
    {
        $this->driver = new VoidCacheDriver($this->prefix);
    }
}

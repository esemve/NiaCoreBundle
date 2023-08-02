<?php

declare(strict_types=1);

namespace Nia\CoreBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class CacheDataCollector extends DataCollector
{
    protected $data = [];

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getName()
    {
        return 'nia.core.cache.data_collector';
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function add(string $cacheKey, string $originalKey, bool $isHit, array $tags): void
    {
        $this->data[] = ['cacheKey' => $cacheKey, 'originalKey' => $originalKey, 'isHit' => $isHit, 'tags' => implode(', ', $tags)];
    }
}

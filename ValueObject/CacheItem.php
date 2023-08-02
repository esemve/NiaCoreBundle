<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

class CacheItem
{
    private $value;
    private $key;
    private $isHit;
    private $ttl;

    public function __construct(string $key, $value, bool $isHit)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(\DateTimeInterface $expiration): self
    {
        $now = new \DateTime();
        $this->ttl = $expiration->getTimestamp() - $now->getTimestamp();

        return $this;
    }

    public function expiresAfter(int $ttl)
    {
        $this->ttl = $ttl;
    }

    public function getTtl(): int
    {
        return $this->ttl ?? 3600;
    }
}

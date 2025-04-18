<?php

namespace App\BusinessLayer\Infra\Cache;

use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use CodeIgniter\Cache\CacheInterface;

class CacheService implements CacheServiceInterface
{
    public CacheInterface $cacheDriver;

    /**
     * @param CacheInterface $cacheDriver
     */
    public function __construct(CacheInterface $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }


    public function save(string $cacheKey, mixed $value, int $ttl = 60): bool
    {
        return $this->cacheDriver->save($cacheKey, $value, $ttl);
    }

    public function get(string $cacheKey): mixed
    {
        return $this->cacheDriver->get($cacheKey);
    }

    public function clean(): bool
    {
        return $this->cacheDriver->clean();
    }

}
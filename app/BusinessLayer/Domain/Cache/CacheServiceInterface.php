<?php

namespace App\BusinessLayer\Domain\Cache;

interface CacheServiceInterface
{
    public function save(string $cacheKey, mixed $value, int $ttl = 60): bool;
    public function get(string $cacheKey): mixed;
    public function clean(): bool;
}
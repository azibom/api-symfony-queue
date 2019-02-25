<?php

namespace App\Util;
 
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
 
interface CacheInterface
{
    public function saveItem(MemcachedAdapter $cachePool, string $key, array $value): bool;
 
    public function getItem(MemcachedAdapter $cachePool, string $key): ?array;
 
    public function deleteItem(MemcachedAdapter $cachePool, string $key): bool;
 
    public function deleteAll(MemcachedAdapter $cachePool): bool;
}
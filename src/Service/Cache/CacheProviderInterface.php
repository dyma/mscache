<?php

namespace DhMs\CacheBundle\Service\Cache;

/**
 * Interface CacheProviderInterface
 * @package DhMs\CacheBundle\Service\Cache
 */
interface CacheProviderInterface
{
    public function get(string $key, $cache_cb = null);

    public function set(string $key, $value, $expire = 0);

    public function delete(string $key);

    public function deleteAll();

    public function invalidate(array $tags);
}
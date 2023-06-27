<?php

namespace DhMs\CacheBundle\Service\Cache;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class CacheProvider
 * @package DhMs\CacheBundle\Service\Cache
 */
class CacheProvider implements CacheProviderInterface
{

    /**
     * Parameter Bag Service.
     * @var Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * Tag Aware Adapter.
     *
     * @var Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    protected $adapter;

    public function __construct(TagAwareAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function get($key, $cache_cb = null)
    {
        $key = $this->prepareCacheKey($key);
        if (!is_callable($cache_cb)) {
            $cache_cb = function () {
                return static::nullValueCallback();
            };
        }
        return $this->adapter->get($key, $cache_cb);
    }

    public function set($key, $value, $expire = 0)
    {
        $key = $this->prepareCacheKey($key);
        $item = $this->adapter->getItem($key);
        $item->set($value);
        $this->adapter->save($item);
    }

    public function deleteAll()
    {
        // @todo: Implement.
    }

    public function delete($key)
    {
        $key = $this->prepareCacheKey($key);
        $this->adapter->delete($key);
        return $this;
    }

    public function invalidate(array $tags)
    {
        $this->adapter->invalidateTags($tags);
        return $this;
    }

    protected function prepareCacheKey(string $key)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '|', $key);
    }

    public static function nullValueCallback()
    {
        return null;
    }

}
<?php

namespace DhMs\CacheBundle\Tests\Unit;

use DhMs\CacheBundle\Service\Cache\CacheProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class CacheProviderTest
 * @package DhMs\CacheBundle\Tests\Unit
 */
class CacheProviderTest extends TestCase
{

    public function testPrepareCacheKey()
    {
        $adapterMock = $this->createMock('\Symfony\Component\Cache\Adapter\RedisTagAwareAdapter');
        $cacheProviderReflection = new \ReflectionClass('DhMs\CacheBundle\Service\Cache\CacheProvider');
        $prepareCacheKey = $cacheProviderReflection->getMethod('prepareCacheKey');
        $prepareCacheKey->setAccessible(TRUE);

        $cacheProvider = new CacheProvider($adapterMock);

        $content = [
            'check{cache}key(value)to/validate\first' => 'check|cache|key|value|to|validate|first',
            'check@cache:key_value//to\validate)second' => 'check|cache|key_value||to|validate|second',
        ];

        foreach ($content as $input => $expected) {
            $result = $prepareCacheKey->invokeArgs($cacheProvider, [$input]);
            $this->assertEquals($result, $expected);
        }
    }

    public function testCacheSetGet()
    {
        $adapterMock = $this->getAdapterMock();

        // Create Cache Provider Object.
        $cacheProvider = new CacheProvider($adapterMock);

        // Test 1.
        $test1 = $cacheProvider->get('key1');
        $test2 = $cacheProvider->get('key2');
        $test3 = $cacheProvider->get('key3');
        $this->assertEquals('Content 1', $test1);
        $this->assertEquals('Content 2', $test2);
        $this->assertEquals('Content 3', $test3);

        // Test 2.
        $newValue = 'Content 4';
        $cacheProvider->set('key4', $newValue);
        $newValueCallback = $cacheProvider->get('key4');
        $this->assertEquals($newValue, $newValueCallback);

        // Test 3.
        $callback_key = 'callback_key';
        $content = $cacheProvider->get($callback_key, function(ItemInterface $item) {
            return 'Callback Value';
        });
        $this->assertEquals('Callback Value', $content);

        // Test 4.
        $callback_value = $cacheProvider->get($callback_key);
        $this->assertEquals('Callback Value', $callback_value);
    }

    public function testCacheInvalidate()
    {
        // Create Cache Provider Object.
        $adapterMock = $this->getAdapterMock();
        $cacheProvider = new CacheProvider($adapterMock);

        // Test 1.
        $content1 = $cacheProvider->get('invalidateKey1');
        $this->assertEquals('Invalidate Content 1', $content1);

        // Test 2.
        $cacheProvider->invalidate(['tag1']);
        $content1 = $cacheProvider->get('invalidateKey1');
        $content2 = $cacheProvider->get('invalidateKey2');
        $this->assertNull($content1);
        $this->assertNull($content2);
    }

    public function testCacheTagAndInvalidate()
    {
        // Create Cache Provider Object.
        $adapterMock = $this->getAdapterMock();
        $cacheProvider = new CacheProvider($adapterMock);

        // Set tagged item
        $key = 'tagged_callback_key';
        $content = $cacheProvider->get($key, function(ItemInterface $item) {
            $item->tag(['tagged_callback_tag']);
            return 'Tagged Callback Value';
        });
        $this->assertEquals('Tagged Callback Value', $content);

        // Get previously set tagged item.
        $content = $cacheProvider->get($key);
        $this->assertEquals('Tagged Callback Value', $content);

        // Invalidate tag and check the the cache cleared.
        $cacheProvider->invalidate(['tagged_callback_tag']);
        $content = $cacheProvider->get($key);
        $this->assertNull($content);
    }

    protected function getAdapterMock(): MockObject
    {
        $adapterMock = $this->createMock('\Symfony\Component\Cache\Adapter\RedisTagAwareAdapter');

        // Mock "getItem" method.
        $adapterMock->method('getItem')->will($this->returnCallback(function ($key) {

            // Get cached content from static storage.
            $cachedContent = static::getCache($key);

            // Create CacheItem object.
            $cacheItem = $this->createCacheItemObject($key);

            // Set cache content value.
            $cacheItem->set($cachedContent);
            return $cacheItem;
        }));

        // Mock "save" method.
        $adapterMock->method('save')->will($this->returnCallback(function (CacheItem $item) {
            static::setCache($item->getKey(), $item->get());
            return true;
        }));

        // Mock "get" method.
        $adapterMock->method('get')->will($this->returnCallback(function ($key, callable $callback) {
            $cachedValue = static::getCache($key);
            if (!empty($cachedValue)) {
                return $cachedValue;
            }
            $cacheItem = $this->createCacheItemObject($key);
            $callbackValue = $callback($cacheItem);
            if (!empty($callbackValue)) {
                static::setCache($key, $callbackValue);
            }
            $tags = $this->getCacheItemNewCacheTags($cacheItem);
            CacheProvideContentStorage::tag($tags, $key);
            return $callbackValue;
        }));

        // Mock "invalidateTags" method.
        $adapterMock->method('invalidateTags')->will($this->returnCallback(function (array $tags) {
            static::invalidateCacheTags($tags);
            return true;
        }));

        return $adapterMock;
    }

    public function getItemMock(): MockObject
    {
        $cacheItemInterfaceMock = $this->createMock('Symfony\Contracts\Cache\CacheItem');
        $cacheItemInterfaceMockClass = get_class($cacheItemInterfaceMock);
    }

    public static function getCache($key)
    {
        $contentStorage = CacheProvideContentStorage::create();
        return $contentStorage::getCache($key);
    }

    public static function setCache($key, $value)
    {
        $contentStorage = CacheProvideContentStorage::create();
        $contentStorage::setCache($key, $value);
    }

    public static function invalidateCacheTags($tags)
    {
        $contentStorage = CacheProvideContentStorage::create();
        $contentStorage::invalidateCacheTags($tags);
    }

    public function createCacheItemMockObject($key): CacheItem
    {

        $cacheItemMock = $this->createMock('Symfony\Component\Cache\CacheItem');
        $cacheItemMockClass = get_class($cacheItemMock);

        // Closure to set protected cache key.
        $createCacheItem = \Closure::bind(static function ($key) use ($cacheItemMockClass) {
            $cacheItem = new $cacheItemMockClass();
            $cacheItem->key = $key;
            return $cacheItem;
        },
            null,
            $cacheItemMockClass
        );
        return $createCacheItem($key);
    }

    public function createCacheItemObject($key): ItemInterface
    {
        // Closure to set protected cache key.
        $createCacheItem = \Closure::bind(static function ($key) {
            $cacheItem = new CacheItem();
            $cacheItem->key = $key;
            $cacheItem->isTaggable = true;
            return $cacheItem;
        },
            null,
            CacheItem::class
        );
        return $createCacheItem($key);
    }

    public function getCacheItemNewCacheTags(CacheItem $item)
    {
        $getNewCacheTags = \Closure::bind(static function (CacheItem $item) {
            return $item->newMetadata[CacheItem::METADATA_TAGS] ?? [];
        },
            null,
            CacheItem::class);
        return $getNewCacheTags($item);
    }

}
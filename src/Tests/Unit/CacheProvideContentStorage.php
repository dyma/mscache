<?php

namespace DhMs\CacheBundle\Tests\Unit;

/**
 * Class CacheProvideContentStorage
 * @package DhMs\CacheBundle\Tests\Unit
 */
class CacheProvideContentStorage
{

    private static $instance = null;

    /**
     * Cached items.
     * @var array $cachedItems
     */
    public static $cachedItems = [
        'key1' => 'Content 1',
        'key2' => 'Content 2',
        'key3' => 'Content 3',
        'invalidateKey1' => 'Invalidate Content 1',
        'invalidateKey2' => 'Invalidate Content 2',
        'invalidateKey3' => 'Invalidate Content 3',
        'invalidateKey4' => 'Invalidate Content 4',
    ];

    public static $cachedTags = [
        'tag1' => [
            'invalidateKey1',
            'invalidateKey2',
        ],
        'tag2' => [
            'invalidateKey3',
            'invalidateKey4',
        ],
    ];

    protected function __construct()
    {
    }

    public static function create()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function getCache($key)
    {
        if (isset(static::$cachedItems[$key])) {
            return static::$cachedItems[$key];
        }
        return NULL;
    }

    public static function setCache($key, $value)
    {
        static::$cachedItems[$key] = $value;
    }

    public static function invalidateCacheTags($tags)
    {
        foreach ($tags as $tag) {
            $keys = isset(static::$cachedTags[$tag]) ? static::$cachedTags[$tag] : [];
            foreach ($keys as $key) {
                if (isset(static::$cachedItems[$key])) {
                    unset(static::$cachedItems[$key]);
                }
            }
        }
    }

    public static function tag(string|iterable $tags, $key)
    {
        foreach ($tags as $tag) {
            static::$cachedTags[$tag][] = $key;
            static::$cachedTags[$tag] = array_unique(static::$cachedTags[$tag]);
        }
    }
}
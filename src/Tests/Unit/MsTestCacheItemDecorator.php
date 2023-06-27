<?php

namespace DhMs\CacheBundle\Tests\Unit;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class MsTestCacheItemDecorator
 * @package DhMs\CacheBundle\Tests\Unit
 */
class MsTestCacheItemDecorator implements ItemInterface
{
    /**
     * @var \Symfony\Contracts\Cache\ItemInterface
     */
    protected $cacheItem;

    public function __construct(ItemInterface $cacheItem)
    {
        $this->cacheItem = $cacheItem;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return $this->cacheItem->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->cacheItem->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return $this->cacheItem->isHit();
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function set($value): static
    {
        $this->cacheItem->set($value);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->cacheItem->expiresAt($expiration);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        $this->cacheItem->expiresAfter($time);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function tag(string|iterable $tags): static
    {
        // Override this method to use test content storage.
        $contentStorage = CacheProvideContentStorage::create();
        $contentStorage::tag($tags, $this->getKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(): array
    {
        return $this->cacheItem->getMetadata();
    }

}
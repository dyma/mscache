<?php

namespace DhMs\CacheBundle\Entity;

/**
 * Interface CacheableEntityInterface
 * @package DhMs\CacheBundle\Entity
 */
interface CacheableEntityInterface
{
    public function getCacheTags(): array;
}

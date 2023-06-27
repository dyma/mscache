<?php

namespace DhMs\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package DhMs\CacheBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('mscache');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->scalarNode('mscache_tag_aware_service')
            ->defaultValue('redis')
            ->end()
            ->scalarNode('memcached_host')
            ->defaultValue('memcached')
            ->end()
            ->scalarNode('memcached_port')
            ->defaultValue('11211')
            ->end()
            ->scalarNode('redis_host')
            ->defaultValue('redis')
            ->end()
            ->scalarNode('redis_port')
            ->defaultValue('6379')
            ->end()
            ->scalarNode('redis_dbindex')
            ->defaultValue('0')
            ->end();

        return $treeBuilder;
    }

}
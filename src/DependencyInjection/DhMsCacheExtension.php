<?php

namespace DhMs\CacheBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DhMsCacheExtension
 * @package DhMs\CacheBundle\DependencyInjection
 */
class DhMsCacheExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {

        // Services.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // Configuration.
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $alias = $this->getAlias();
        foreach ($config as $key => $value) {
            $container->setParameter($alias . '.' . $key, $value);
        }
    }

    public function getAlias(): string
    {
        return 'dh_ms_cache';
    }

}
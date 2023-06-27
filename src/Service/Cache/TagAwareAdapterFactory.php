<?php

namespace DhMs\CacheBundle\Service\Cache;

use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class TagAwareAdapterFactory
 * @package DhMs\CacheBundle\Service\Cache
 */
class TagAwareAdapterFactory
{

    /**
     * Parameter Bag.
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    protected $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function createAdapter(): TagAwareAdapterInterface
    {

        $service = $this->parameterBag->get('dh_ms_cache.mscache_tag_aware_service');

        switch ($service) {

            case 'redis':
                return $this->buildRedisService();

            case 'file':
                return $this->buildFileService();

            default:
                throw new \Exception('MsCache: the cache tag aware service is not defined.');

        }
    }

    protected function buildRedisService()
    {
        $host = $this->parameterBag->get('redis_host');
        $port = $this->parameterBag->get('redis_port');
        $dbindex = $this->parameterBag->get('redis_dbindex');
        $client = RedisAdapter::createConnection("redis://$host:$port/$dbindex");
        return new RedisTagAwareAdapter($client);
    }

    protected function buildFileService()
    {
        return new FilesystemTagAwareAdapter();
    }

}

<?php

namespace DhMs\CacheBundle\EventSubscriber;

use DhMs\CacheBundle\Entity\CacheableEntityInterface;
use DhMs\CacheBundle\Service\Cache\CacheProviderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CacheableEntityUpdateSubscriber
 * @package DhMs\CacheBundle\EventSubscriber
 */
class CacheableEntityUpdateSubscriber implements EventSubscriberInterface
{

    /**
     * Cache Provider Service.
     *
     * @var CacheProviderInterface
     */
    protected $cacheProvider;

    public function __construct(CacheProviderInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public static function getSubscribedEvents(): array
    {
        if (!class_exists(AfterEntityDeletedEvent::class)) {
            return [];
        }

        return [
            AfterEntityDeletedEvent::class => ['invalidateEntityTags'],
            AfterEntityUpdatedEvent::class => ['invalidateEntityTags'],
            AfterEntityPersistedEvent::class => ['invalidateEntityTags'],
        ];
    }

    public function invalidateEntityTags(EntityLifecycleEventInterface $event)
    {
        $entity = $event->getEntityInstance();
        if ($entity instanceof CacheableEntityInterface) {
            $tags = $entity->getCacheTags();
            $this->cacheProvider->invalidate($tags);
        }
    }

}

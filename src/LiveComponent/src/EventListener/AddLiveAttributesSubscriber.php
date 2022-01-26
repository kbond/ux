<?php

namespace Symfony\UX\LiveComponent\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\UX\LiveComponent\Twig\LiveComponentRuntime;
use Symfony\UX\TwigComponent\EventListener\PreRenderEvent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AddLiveAttributesSubscriber implements EventSubscriberInterface
{
    public function __construct(private LiveComponentRuntime $runtime)
    {
    }

    public function onPreRender(PreRenderEvent $event): void
    {
        if (!isset($event->mountedComponent->config()['live'])) {
            // not a live component, skip
            return;
        }

        $event->mountedComponent->attributes = $event->mountedComponent->attributes->merge(
            $this->runtime->getLiveAttributes($event->mountedComponent)->all()
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [PreRenderEvent::class => 'onPreRender'];
    }
}

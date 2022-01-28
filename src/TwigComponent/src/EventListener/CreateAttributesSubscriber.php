<?php

namespace Symfony\UX\TwigComponent\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\UX\TwigComponent\ComponentAttributes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CreateAttributesSubscriber implements EventSubscriberInterface
{
    public function onPostMount(PostMountEvent $event): void
    {
        $attributes = $event->data['attributes'] ?? [];
        unset($event->data['attributes']);

        foreach ($event->data as $key => $value) {
            if (is_scalar($value)) {
                $attributes[$key] = $value;
                unset($event->data[$key]);
            }
        }

        $event->extraData['attributes'] = new ComponentAttributes($attributes);
    }

    public static function getSubscribedEvents(): array
    {
        return [PostMountEvent::class => ['onPostMount', -1000]];
    }
}

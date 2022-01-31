<?php

namespace Symfony\UX\LiveComponent\Hydrator;

use Symfony\UX\LiveComponent\Exception\UnsupportedHydrationException;
use Symfony\UX\LiveComponent\PropertyHydratorInterface;
use Symfony\UX\TwigComponent\ComponentAttributes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComponentAttributesPropertyHydrator implements PropertyHydratorInterface
{
    public function dehydrate($value)
    {
        if ($value instanceof ComponentAttributes) {
            return ['_attributes' => $value->all()];
        }

        throw new UnsupportedHydrationException();
    }

    public function hydrate(string $type, $value)
    {
        if ('array' === $type && isset($value['_attributes']) && is_array($value['_attributes'])) {
            return new ComponentAttributes($value['_attributes']);
        }

        throw new UnsupportedHydrationException();
    }
}

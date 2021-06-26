<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent\Attribute;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsLiveComponent extends AsTwigComponent
{
    /**
     * @internal
     *
     * @param string|object $classOrObject
     *
     * @return LivePropContext[]
     */
    public static function liveProps($classOrObject): \Traversable
    {
        foreach (self::propertiesFor($classOrObject) as $property) {
            if ($attribute = $property->getAttributes(LiveProp::class)[0] ?? null) {
                yield new LivePropContext($attribute->newInstance(), $property);
            }
        }
    }

    /**
     * @internal
     *
     * @param string|object $classOrObject
     */
    public static function isActionAllowed($classOrObject, string $action): bool
    {
        foreach (self::attributeMethodsFor(LiveAction::class, $classOrObject) as $method) {
            if ($action === $method->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     *
     * @param string|object $classOrObject
     *
     * @return \ReflectionMethod[]
     */
    public static function beforeReRenderMethods($classOrObject): \Traversable
    {
        yield from self::attributeMethodsFor(BeforeReRender::class, $classOrObject);
    }

    /**
     * @internal
     *
     * @param string|object $classOrObject
     *
     * @return \ReflectionMethod[]
     */
    public static function postHydrateMethods($classOrObject): \Traversable
    {
        yield from self::attributeMethodsFor(PostHydrate::class, $classOrObject);
    }

    /**
     * @internal
     *
     * @param string|object $classOrObject
     *
     * @return \ReflectionMethod[]
     */
    public static function preDehydrateMethods($classOrObject): \Traversable
    {
        yield from self::attributeMethodsFor(PreDehydrate::class, $classOrObject);
    }

    /**
     * @param string|object $classOrObject
     *
     * @return \ReflectionMethod[]
     */
    private static function attributeMethodsFor(string $attribute, $classOrObject): \Traversable
    {
        foreach ((new \ReflectionClass($classOrObject))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getAttributes($attribute)[0] ?? null) {
                yield $method;
            }
        }
    }

    /**
     * @param string|object $classOrObject
     *
     * @return \ReflectionProperty[]
     */
    private static function propertiesFor($classOrObject): \Traversable
    {
        $class = $classOrObject instanceof \ReflectionClass ? $classOrObject : new \ReflectionClass($classOrObject);

        foreach ($class->getProperties() as $property) {
            yield $property;
        }

        if ($parent = $class->getParentClass()) {
            yield from self::propertiesFor($parent);
        }
    }
}

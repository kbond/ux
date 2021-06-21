<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Attribute;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class TwigComponent
{
    private string $name;
    private ?string $template;

    public function __construct(string $name, ?string $template = null)
    {
        $this->name = $name;
        $this->template = $template;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getTemplate(): string
    {
        return $this->template ?? "components/{$this->name}.html.twig";
    }

    /**
     * @internal
     */
    final public static function forClass(string $class): ?static
    {
        $class = new \ReflectionClass($class);

        if (!$attribute = $class->getAttributes(self::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null) {
            return null;
        }

        return $attribute->newInstance();
    }
}

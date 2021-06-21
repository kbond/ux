<?php

namespace Symfony\UX\TwigComponent\Attribute;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplate(): string
    {
        return $this->template ?? "components/{$this->name}.html.twig";
    }

    /**
     * @internal
     */
    public static function forClass(string $class): ?self
    {
        $class = new \ReflectionClass($class);

        if (!$attribute = $class->getAttributes(self::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null) {
            return null;
        }

        return $attribute->newInstance();
    }
}

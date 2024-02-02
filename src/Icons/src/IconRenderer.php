<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons;

use Twig\Environment;
use Twig\Extension\EscaperExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconRenderer
{
    public function __construct(
        private IconRegistryInterface $registry,
        private array $defaultIconAttributes = [],
    ) {
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    public function renderIcon(Environment $twig, string $name, array $attributes = []): string
    {
        [$content, $iconAttr] = $this->registry->get($name);

        $iconAttr = array_merge($iconAttr, $this->defaultIconAttributes);

        return sprintf(
            '<svg%s>%s</svg>',
            self::normalizeAttributes($twig, [...$iconAttr, ...$attributes]),
            $content,
        );
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    private static function normalizeAttributes(Environment $twig, array $attributes): string
    {
        return array_reduce(
            array_keys($attributes),
            static function (string $carry, string $key) use ($attributes, $twig) {
                $value = $attributes[$key];

                return match ($value) {
                    true => "{$carry} {$key}",
                    false => $carry,
                    default => sprintf('%s %s="%s"', $carry, $key, self::escapeAsHtmlAttr($twig, $value)),
                };
            },
            ''
        );
    }

    private static function escapeAsHtmlAttr(Environment $twig, mixed $value): string
    {
        if (method_exists(EscaperExtension::class, 'escape')) {
            return EscaperExtension::escape($twig, $value, 'html_attr');
        }

        // since twig/twig 3.9.0: Using the internal "twig_escape_filter" function is deprecated.
        return twig_escape_filter($twig, $value, 'html_attr');
    }
}

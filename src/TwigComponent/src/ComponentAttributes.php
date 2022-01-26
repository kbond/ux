<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class ComponentAttributes
{
    /**
     * @param array<string, string> $attributes
     */
    public function __construct(private array $attributes = [])
    {
    }

    public function __toString(): string
    {
        return array_reduce(
            array_keys($this->attributes),
            fn (string $carry, string $key) => sprintf('%s %s="%s"', $carry, $key, $this->attributes[$key]),
            ''
        );
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * @immutable
     */
    public function merge(array $with): self
    {
        return new self(array_merge($this->attributes, $with));
    }

    /**
     * Set default attributes. If the attribute isn't currently defined,
     * the value from $defaults is used. The exception is "class". For
     * this attribute, the value is prepended.
     *
     * @immutable
     */
    public function defaults(array $defaults): self
    {
        foreach ($this->attributes as $key => $value) {
            // "class" is special so we prepend defaults
            $defaults[$key] = isset($defaults[$key]) && 'class' === $key ? "{$defaults[$key]} {$value}" : $value;
        }

        return new self($defaults);
    }
}

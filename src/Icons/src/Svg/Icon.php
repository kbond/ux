<?php

namespace Symfony\UX\Svg;

/**
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class Icon implements \Stringable, \Serializable, \ArrayAccess
{
    public function __construct(
        private readonly string $innerSvg,
        private readonly array $attributes = [],
    )
    {
        // @todo validate attributes (?)
        // the main idea is to have a way to validate the attributes
        // before the icon is cached to improve performances
        // (avoiding to validate the attributes each time the icon is rendered)
    }

    public function toHtml(): string
    {
        $htmlAttributes = '';
        foreach ($this->attributes as $name => $value) {
            if (false === $value) {
                continue;
            }
            $htmlAttributes .= ' '.$name;
            if (true !== $value) {
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $htmlAttributes .= '="'. $value .'"';
            }
        }

        return '<svg'.$htmlAttributes.'>'.$this->innerSvg.'</svg>';
    }

    public function getInnerSvg(): string
    {
        return $this->innerSvg;
    }

    /**
     * @param array<string, string|bool> $attributes
     * @return self
     */
    public function withAttributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException(sprintf('Attribute names must be string, "%s" given.', get_debug_type($name)));
            }
            // @todo regexp would be better ?
            if (!ctype_alnum($name) && !str_contains($name, '-')) {
                throw new \InvalidArgumentException(sprintf('Invalid attribute name "%s".', $name));
            }
            if (!is_string($value) && !is_bool($value)) {
                throw new \InvalidArgumentException(sprintf('Invalid value type for attribute "%s". Boolean or string allowed, "%s" provided. ', $name, get_debug_type($value)));
            }
        }

        return new self($this->innerSvg, [...$this->attributes, ...$attributes]);
    }

    public function withInnerSvg(string $innerSvg): self
    {
        // @todo validate svg ?

        // The main idea is to not validate the attributes for every icon
        // when they come from a pack (and thus share a set of attributes)

        return new self($innerSvg, $this->attributes);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    public function serialize(): string
    {
        return serialize([$this->innerSvg, $this->attributes]);
    }

    public function unserialize(string $data): void
    {
        [$this->innerSvg, $this->attributes] = unserialize($data);
    }

    public function __serialize(): array
    {
        return [$this->innerSvg, $this->attributes];
    }

    public function __unserialize(array $data): void
    {
        [$this->innerSvg, $this->attributes] = $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('The Icon object is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('The Icon object is immutable.');
    }
}

<?php

namespace Symfony\UX\Icons\Registry;

use Symfony\UX\Icons\Icon;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class AliasIconRegistry implements IconRegistryInterface
{
    /**
     * @param array<string, string> $aliasMap
     */
    public function __construct(private IconRegistryInterface $inner, private array $aliasMap)
    {
    }

    public function get(string $name): Icon
    {
        return $this->inner->get($this->aliasMap[$name] ?? $name);
    }

    /**
     * @return list<string>
     */
    public function aliases(): array
    {
        return array_keys($this->aliasMap);
    }
}

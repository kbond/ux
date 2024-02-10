<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Registry;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\IconRegistryInterface;
use Symfony\UX\Icons\Svg\Icon;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class CacheIconRegistry implements IconRegistryInterface, CacheWarmerInterface
{
    /**
     * @param IconRegistryInterface[] $registries
     */
    public function __construct(private \Traversable $registries, private CacheInterface $cache)
    {
    }

    public function get(string $name, bool $refresh = false): Icon
    {
        if (!Icon::isValidName($name)) {
            throw new IconNotFoundException(sprintf('The icon name "%s" is not valid.', $name));
        }

        return $this->cache->get(
            sprintf('ux-icon-%s', Icon::nameToId($name)),
            function () use ($name) {
                foreach ($this->registries as $registry) {
                    try {
                        return $registry->get($name);
                    } catch (IconNotFoundException) {
                        // ignore
                    }
                }

                throw new IconNotFoundException(sprintf('The icon "%s" does not exist.', $name));
            },
            beta: $refresh ? \INF : null,
        );
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->registries as $registry) {
            yield from $registry;
        }
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        foreach ($this as $name) {
            $this->get($name, refresh: true);
        }

        return [];
    }
}

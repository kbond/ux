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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconPack implements \Countable
{
    /**
     * @param array<string,string> $metadata
     */
    public function __construct(
        public readonly string $prefix,
        private int $count,
        public readonly array $metadata = [],
    ) {
    }

    public function count(): int
    {
        return $this->count;
    }
}

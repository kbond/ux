<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\EventListener;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class PostMountEvent extends Event
{
    public array $extraData = [];

    public function __construct(public array $data)
    {
        // todo - use getters/setters
        // todo - $data/$extraData is confusing...
    }
}

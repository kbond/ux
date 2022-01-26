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
final class MountedComponent
{
    public string $template;

    public function __construct(
        public object $component,
        public ComponentAttributes $attributes,
        private array $config
    ) {
        $this->template = $this->config['template'];
    }

    public function config(): array
    {
        return $this->config;
    }
}

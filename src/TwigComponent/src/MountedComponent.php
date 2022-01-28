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
 *
 * @internal
 */
final class MountedComponent
{
    public function __construct(
        private object $component,
        private ComponentAttributes $attributes,
        private ComponentMetadata $metadata
    ) {
    }

    public function getComponent(): object
    {
        return $this->component;
    }

    public function getAttributes(): ComponentAttributes
    {
        return $this->attributes;
    }

    public function getMetadata(): ComponentMetadata
    {
        return $this->metadata;
    }

    public function getVariables(): array
    {
        return array_merge(
            ['this' => $this->component, 'attributes' => $this->attributes],
            get_object_vars($this->component)
        );
    }
}

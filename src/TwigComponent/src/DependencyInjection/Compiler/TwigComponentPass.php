<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TwigComponentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('twig.component')) as $id) {
            // component services must not be shared
            $container->findDefinition($id)->setShared(false);
        }
    }
}

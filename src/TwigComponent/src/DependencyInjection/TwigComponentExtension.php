<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\Twig\ComponentExtension;
use Symfony\UX\TwigComponent\Twig\ComponentRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class TwigComponentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register('ux.twig.component_locator', ServiceLocator::class)
            ->addTag('container.service_locator')
        ;

        $container->register(ComponentFactory::class)
            ->setArguments([
                new Reference('ux.twig.component_locator'),
                new Reference('property_accessor'),
            ])
        ;

        $container->register(ComponentRenderer::class)
            ->setArguments([
                new Reference('twig'),
            ])
        ;

        $container->register(ComponentExtension::class)
            ->addTag('twig.extension')
        ;

        $container->register(ComponentRuntime::class)
            ->setArguments([
                new Reference(ComponentFactory::class),
                new Reference(ComponentRenderer::class),
            ])
            ->addTag('twig.runtime')
        ;
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\UX\Icons\Command\WarmIconCacheCommand;
use Symfony\UX\Icons\IconRenderer;
use Symfony\UX\Icons\IconStack;
use Symfony\UX\Icons\Registry\CacheIconRegistry;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;
use Symfony\UX\Icons\Twig\UXIconExtension;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.ux_icons.cache_icon_registry', CacheIconRegistry::class)
            ->args([
                iterator([service('.ux_icons.local_svg_icon_registry')]),
                abstract_arg('cache'),
            ])

        ->set('.ux_icons.local_svg_icon_registry', LocalSvgIconRegistry::class)
            ->args([
                abstract_arg('icon_dir'),
            ])

        ->alias('.ux_icons.icon_registry', '.ux_icons.cache_icon_registry')

        ->set('.ux_icons.command.warm_icon_cache', WarmIconCacheCommand::class)
            ->args([
                service('.ux_icons.cache_icon_registry'),
            ])
            ->tag('console.command')

        ->set('.ux_icons.icon_stack', IconStack::class)
            ->tag('kernel.reset', ['method' => 'reset'])

        ->set('.ux_icons.twig_icon_extension', UXIconExtension::class)
            ->tag('twig.extension')

        ->set('.ux_icons.icon_renderer', IconRenderer::class)
            ->args([
                service('.ux_icons.icon_registry'),
                service('.ux_icons.icon_stack'),
            ])
            ->tag('twig.runtime')
    ;
};

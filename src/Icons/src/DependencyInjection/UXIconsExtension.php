<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\DependencyInjection;

use Symfony\Component\AssetMapper\Event\PreAssetsCompileEvent;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class UXIconsExtension extends ConfigurableExtension implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ux_icons');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('icon_dir')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) { return [$v]; })
                    ->end()
                    ->info(<<<EOF
                        The local directory('s) where icons are stored.
                        Order matters as the first directory to contain the icon will be used.
                        The first directory will be used to store imported icons.
                        EOF)
                    ->scalarPrototype()->end()
                    ->defaultValue(['%kernel.project_dir%/assets/icons'])
                ->end()
                ->variableNode('default_icon_attributes')
                    ->info('Default attributes to add to all icons.')
                    ->defaultValue(['fill' => 'currentColor'])
                ->end()
                ->arrayNode('iconify')
                    ->info('Configuration for the "on demand" icons powered by Iconify.design.')
                    ->{interface_exists(HttpClientInterface::class) ? 'canBeDisabled' : 'canBeEnabled'}()
                    ->children()
                        ->scalarNode('endpoint')
                            ->info('The endpoint for the Iconify API.')
                            ->defaultValue('https://api.iconify.design')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        if (isset($container->getParameter('kernel.bundles')['TwigComponentBundle'])) {
            $loader->load('twig_component.php');
        }

        if (class_exists(PreAssetsCompileEvent::class)) {
            $loader->load('asset_mapper.php');
        }

        $container->getDefinition('.ux_icons.local_svg_icon_registry')
            ->setArguments([
                $mergedConfig['icon_dir'],
            ])
        ;

        $container->getDefinition('.ux_icons.icon_renderer')
            ->setArgument(1, $mergedConfig['default_icon_attributes'])
        ;

        if ($mergedConfig['iconify']['enabled']) {
            $loader->load('iconify.php');

            $container->getDefinition('.ux_icons.iconify')
                ->setArgument(0, $mergedConfig['iconify']['endpoint'])
            ;
        }

        if (!$container->getParameter('kernel.debug')) {
            $container->removeDefinition('.ux_icons.command.import');
        }
    }
}

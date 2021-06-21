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
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\UX\TwigComponent\Attribute\TwigComponent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class TwigComponentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $serviceIdMap = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            if (!\class_exists($class) || !$attribute = TwigComponent::forClass($class)) {
                continue;
            }

            $name = $attribute->getName();

            // make all component services non-shared
            $definition->setShared(false);

            // ensure component not already defined
            if (\array_key_exists($name, $serviceIdMap)) {
                throw new LogicException(sprintf('Component "%s" is already registered as "%s", components cannot be registered more than once.', $definition->getClass(), $serviceIdMap[$name]));
            }

            $serviceIdMap[$name] = new Reference($id);

            // add a consistent alias for use by LiveComponent
            $container->setAlias("ux.twig.component.{$name}", $id);
        }

        $container->findDefinition('ux.twig.component_locator')
            ->setArgument(0, $serviceIdMap)
        ;
    }
}

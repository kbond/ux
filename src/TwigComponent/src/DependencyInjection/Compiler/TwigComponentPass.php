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

use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TwigComponentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $componentMap = [];

        foreach (array_keys($container->findTaggedServiceIds('twig.component')) as $id) {
            $componentDefinition = $container->findDefinition($id);

            $attributes = (new \ReflectionClass($componentDefinition->getClass()))
                ->getAttributes(AsTwigComponent::class, \ReflectionAttribute::IS_INSTANCEOF)
            ;

            if (!isset($attributes[0])) {
                throw new LogicException(sprintf('Service "%s" is tagged as a "twig.component" but does not have a "%s" class attribute.', $id, AsTwigComponent::class));
            }

            /** @var AsTwigComponent $attribute */
            $attribute = $attributes[0]->newInstance();

            $componentMap[$attribute->getName()] = new Reference($id);

            // component services must not be shared
            $componentDefinition->setShared(false);
        }

        $container->findDefinition(ComponentFactory::class)
            ->setArgument(0, new ServiceLocatorArgument($componentMap))
        ;
    }
}

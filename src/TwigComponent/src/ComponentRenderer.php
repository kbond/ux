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

use Twig\Environment;
use Twig\Extension\EscaperExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class ComponentRenderer
{
    private bool $safeClassesRegistered = false;

    public function __construct(private Environment $twig)
    {
    }

    public function render(MountedComponent $mountedComponent): string
    {
        if (!$this->safeClassesRegistered) {
            $this->twig->getExtension(EscaperExtension::class)->addSafeClass(ComponentAttributes::class, ['html']);

            $this->safeClassesRegistered = true;
        }

        return $this->twig->render($mountedComponent->template, array_merge(
            [
                'this' => $mountedComponent->component,
                'attributes' => $mountedComponent->attributes,
                '_component_config' => $mountedComponent->config(),
            ],
            get_object_vars($mountedComponent->component)
        ));
    }
}

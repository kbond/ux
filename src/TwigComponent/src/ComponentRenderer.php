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

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\TwigComponent\EventListener\PreRenderEvent;
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

    public function __construct(private Environment $twig, private EventDispatcherInterface $dispatcher)
    {
    }

    public function render(MountedComponent $mounted): string
    {
        if (!$this->safeClassesRegistered) {
            $this->twig->getExtension(EscaperExtension::class)->addSafeClass(ComponentAttributes::class, ['html']);

            $this->safeClassesRegistered = true;
        }

        $this->dispatcher->dispatch($event = new PreRenderEvent($mounted));

        return $this->twig->render($event->getTemplate(), $event->getVariables());
    }
}

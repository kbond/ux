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

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class ComponentRenderer
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render(object $component): string
    {
        // TODO: Self-Rendering components?
        if (!$attribute = AsTwigComponent::forClass($component::class)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a Twig Component, did you forget to add the AsTwigComponent attribute?', $component::class));
        }

        return $this->twig->render($attribute->getTemplate(), ['this' => $component]);
    }
}

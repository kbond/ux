<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\UX\LiveComponent\LiveComponentHydrator;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\MountedComponent;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class LiveComponentRuntime
{
    public function __construct(
        private Environment $twig,
        private LiveComponentHydrator $hydrator,
        private ComponentFactory $factory,
        private UrlGeneratorInterface $urlGenerator,
        private ?CsrfTokenManagerInterface $csrfTokenManager = null
    ) {
    }

    public function getComponentUrl(string $name, array $props = []): string
    {
        $mounted = $this->factory->create($name, $props);
        $params = ['component' => $name] + $this->hydrator->dehydrate($mounted);

        return $this->urlGenerator->generate('live_component', $params);
    }

    public function getLiveAttributes(MountedComponent $mounted): ComponentAttributes
    {
        $url = $this->urlGenerator->generate('live_component', ['component' => $mounted->getMetadata()->getName()]);
        $data = $this->hydrator->dehydrate($mounted);

        $attributes = [
            'data-controller' => 'live',
            'data-live-url-value' => twig_escape_filter($this->twig, $url, 'html_attr'),
            'data-live-data-value' => twig_escape_filter($this->twig, json_encode($data, \JSON_THROW_ON_ERROR), 'html_attr'),
        ];

        if ($this->csrfTokenManager) {
            $attributes['data-live-csrf-value'] = $this->csrfTokenManager->getToken($mounted->getMetadata()->getName())->getValue();
        }

        return new ComponentAttributes($attributes);
    }
}

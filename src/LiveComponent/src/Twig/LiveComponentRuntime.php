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

    public function renderLiveAttributes(array $context): string
    {
        if (!isset($context['_mounted_component'])) {
            throw new \LogicException('init_live_component can only be called within a component template.');
        }

        return $this->getLiveAttributes($context['_mounted_component']);
    }

    public function getComponentUrl(object $component, string $name = null): string
    {
        $data = $this->hydrator->dehydrate($component);
        $params = ['component' => $this->factory->configFor($name ?? $component)] + $data;

        return $this->urlGenerator->generate('live_component', $params);
    }

    public function getLiveAttributes(MountedComponent $mounted): ComponentAttributes
    {
        if (!isset($mounted->config()['live'])) {
            throw new \LogicException(sprintf('"%s" is not a Live Component.', $mounted->config()['class']));
        }

        $url = $this->urlGenerator->generate('live_component', ['component' => $mounted->config()['name']]);
        $data = $this->hydrator->dehydrate($mounted);

        $attributes = [
            'data-controller' => 'live',
            'data-live-url-value' => twig_escape_filter($this->twig, $url, 'html_attr'),
            'data-live-data-value' => twig_escape_filter($this->twig, json_encode($data, \JSON_THROW_ON_ERROR), 'html_attr'),
        ];

        if ($this->csrfTokenManager) {
            $attributes['data-live-csrf-value'] = $this->csrfTokenManager->getToken($mounted->config()['name'])->getValue();
        }

        return new ComponentAttributes($attributes);
    }
}

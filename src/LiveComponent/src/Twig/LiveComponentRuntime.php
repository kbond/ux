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
    private LiveComponentHydrator $hydrator;
    private ComponentFactory $factory;
    private UrlGeneratorInterface $urlGenerator;
    private ?CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(LiveComponentHydrator $hydrator, ComponentFactory $factory, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager = null)
    {
        $this->hydrator = $hydrator;
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function renderLiveAttributes(Environment $env, array $context): string
    {
        if (!isset($context['_mounted_component'])) {
            throw new \LogicException('init_live_component can only be called within a component template.');
        }

        /** @var MountedComponent $mountedComponent */
        $mountedComponent = $context['_mounted_component'];

        if (!isset($mountedComponent->config()['live'])) {
            throw new \LogicException(sprintf('"%s" is not a Live Component.', $mountedComponent->config()['class']));
        }

        $name = $mountedComponent->config()['name'];
        $url = $this->urlGenerator->generate('live_component', ['component' => $name]);
        $data = $this->hydrator->dehydrate($mountedComponent);

        $ret = sprintf(
            'data-controller="live" data-live-url-value="%s" data-live-data-value="%s"',
            twig_escape_filter($env, $url, 'html_attr'),
            twig_escape_filter($env, json_encode($data, \JSON_THROW_ON_ERROR), 'html_attr'),
        );

        if (!$this->csrfTokenManager) {
            return $ret;
        }

        return sprintf('%s data-live-csrf-value="%s"',
            $ret,
            $this->csrfTokenManager->getToken($name)->getValue()
        );
    }

    public function getComponentUrl(object $component, string $name = null): string
    {
        $data = $this->hydrator->dehydrate($component);
        $params = ['component' => $this->factory->configFor($name ?? $component)] + $data;

        return $this->urlGenerator->generate('live_component', $params);
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class BatchActionController
{
    public function __construct(private HttpKernelInterface $kernel)
    {
    }

    public function __invoke(Request $request): void
    {
        $actions = $request->attributes->get('_component_data')['actions'] ?? throw new BadRequestHttpException();
        $serviceId = $request->attributes->get('_component_service_id');
        $attributes = $request->attributes->all();

        unset($attributes['_component_service_id']);

        foreach ($actions as $action) {
            $name = $action['name'] ?? throw new BadRequestHttpException('Invalid JSON');

            $subRequest = $request->duplicate(attributes: \array_merge($attributes, [
                '_controller' => [$serviceId, $name],
                '_component_action_args' => $action['args'] ?? [],
            ]));

            $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

            // todo handle redirects
            // todo handle exceptions
        }
    }
}

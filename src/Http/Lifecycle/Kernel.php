<?php
/**
 * Kernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;


use Dybasedev\Keeper\Http\Response;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Kernel
 *
 * 生命周期管理器核心类
 *
 * @package Dybasedev\Keeper\Http\Lifecycle
 */
class Kernel
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Kernel constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->instance(static::class, $this);
    }

    /**
     * @param $response
     *
     * @return Response
     */
    public function prepareResponse($response)
    {
        if (!$response instanceof Response) {
            if ($response instanceof SymfonyResponse) {
                $response = new Response($response->getContent(), $response->getStatusCode(),
                    $response->headers->all());
            } elseif (is_array($response)) {
                return $this->prepareResponse(new JsonResponse($response));
            } else {
                $response = new Response($response);
            }
        }

        return $response;
    }
}
<?php
/**
 * Kernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;


use Dybasedev\Keeper\Http\Request;
use Dybasedev\Keeper\Http\Response;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Kernel
 *
 * 生命周期管理器
 *
 * @package Dybasedev\Keeper\Http\Lifecycle
 */
class Handler
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var RouteDispatcher
     */
    protected $routeDispatcher;

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
     * 初始化
     *
     * 一般的，初始化的过程是在 Worker 进程中进行，主要进行一系列需要常驻内存的数据、模块注册
     *
     * @param RouteDispatcher $dispatcher
     */
    public function init(RouteDispatcher $dispatcher)
    {
        $this->routeDispatcher = $dispatcher;
    }

    /**
     * 请求调度
     *
     * @param Request $request
     *
     * @return Response
     */
    public function dispatch(Request $request)
    {
        return $this->prepareResponse($this->routeDispatcher->dispatch($request));
    }

    /**
     * 响应预处理
     *
     * @param string|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return Response 输出一个 Dybasedev\Keeper\Http\Response 对象
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

    /**
     * 获取 Container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
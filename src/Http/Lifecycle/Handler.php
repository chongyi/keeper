<?php
/**
 * Kernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;

use Closure;
use Dybasedev\Keeper\Http\Request;
use Dybasedev\Keeper\Http\Response;
use Dybasedev\Keeper\Process\Exceptions\RuntimeException;
use Exception;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @var Closure
     */
    protected $exceptionHandler;

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
     * 设置 Route Dispatcher
     *
     * @param RouteDispatcher $dispatcher
     */
    public function setRouteDispatcher(RouteDispatcher $dispatcher)
    {
        $this->routeDispatcher = $dispatcher;
    }

    /**
     * @return RouteDispatcher
     */
    public function getRouteDispatcher()
    {
        return $this->routeDispatcher;
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
        try {
            return $this->prepareResponse($this->getRouteDispatcher()->dispatch($request));
        } catch (Exception $exception) {
            return $this->prepareResponse($this->handleException($exception));
        }
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

    /**
     * 设置异常处理器
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function setExceptionHandler(Closure $callback)
    {
        $this->exceptionHandler = $callback;

        return $this;
    }

    /**
     * @param Exception $exception
     *
     * @return SymfonyResponse
     *
     * @throws RuntimeException
     */
    public function handleException(Exception $exception)
    {
        // 所有异常统一转换为 Http 异常，
        // 异常处理器有必要根据其返回一个合理的 Http 响应
        if (!$exception instanceof HttpException) {
            $exception = new HttpException(500, $exception->getMessage(), $exception);
        }

        if (is_null($this->exceptionHandler)) {
            return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        }

        $response = call_user_func($this->exceptionHandler, $exception);

        if ($response instanceof SymfonyResponse) {
            return $response;
        }

        throw new RuntimeException();
    }

}
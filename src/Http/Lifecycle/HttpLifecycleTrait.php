<?php
/**
 * HttpServiceLifecycleTrait.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;

use Dybasedev\Keeper\Http\Lifecycle\Interfaces\RouteDispatcher;
use Dybasedev\Keeper\Http\Request;
use Dybasedev\Keeper\Http\ServerProcess;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;

/**
 * Trait HttpServiceLifecycleTrait
 *
 * Http 服务生命周期 Trait
 *
 * @package Dybasedev\Keeper\Http\Lifecycle
 */
trait HttpLifecycleTrait
{
    /**
     * @var Handler
     */
    protected $lifecycleHandler;

    /**
     * 当 Worker 启动时触发
     */
    public function onWorkerStart()
    {
        // 注册 ServerProcess
        $this->getContainer()->instance(ServerProcess::class, $this);
        $this->getContainer()->instance('workerId', $this->getWorkerId());

        $this->lifecycleHandler = $this->createLifecycleHandler($this->getContainer());
        $this->lifecycleHandler->setExceptionHandler($this->getExceptionHandler())
                               ->setRouteDispatcher($this->getRouteDispatcher($this->lifecycleHandler)
                                                         ->routesRegistrar($this->getRoutesRegistrar()));
    }

    /**
     * 创建生命周期管理器
     *
     * @param ContainerInterface $container
     *
     * @return Handler
     */
    public function createLifecycleHandler(ContainerInterface $container)
    {
        return new Handler($container);
    }

    /**
     * 当请求进入时触发
     *
     * @param SwooleRequest  $origin
     * @param SwooleResponse $responseControl
     */
    public function onRequest(SwooleRequest $origin, SwooleResponse $responseControl)
    {
        $request = Request::createFromSwooleRequest($origin);

        $this->lifecycleHandler->dispatch($request)->setSwooleResponse($responseControl)->send();
    }

    /**
     * @return Container
     */
    abstract public function getContainer();

    /**
     * 获取 Worker ID
     *
     * @return int
     */
    abstract public function getWorkerId();

    /**
     * 获取路由调度器
     *
     * @param Handler $handler
     *
     * @return RouteDispatcher
     */
    abstract protected function getRouteDispatcher(Handler $handler);

    /**
     * 获取路由注册器
     *
     * @return \Closure
     */
    abstract protected function getRoutesRegistrar();

    /**
     * 异常处理器
     *
     * @return \Closure
     */
    abstract protected function getExceptionHandler();
}
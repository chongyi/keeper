<?php
/**
 * RouteDispatcher.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle\Illuminate;


use Closure;
use Dybasedev\Keeper\Http\Lifecycle\Handler;
use Dybasedev\Keeper\Http\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Dybasedev\Keeper\Http\Lifecycle\RouteDispatcher as RouteDispatcherInterface;

class RouteDispatcher implements RouteDispatcherInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var DispatcherInterface;
     */
    protected $events;

    /**
     * IlluminateRouteDispatch constructor.
     *
     * @param Handler $kernel
     */
    public function __construct(Handler $kernel)
    {
        $this->handler   = $kernel;
        $this->container = $kernel->getContainer();

        $this->events = new Dispatcher($this->container);
        $this->container->instance(DispatcherInterface::class, $this->events);
        $this->router = new Router($this->events, $this->container);
        $this->container->instance(Router::class, $this->router);
    }

    /**
     * @inheritDoc
     */
    public function dispatch(Request $request)
    {
        return $this->router->dispatch($request);
    }

    /**
     * @inheritDoc
     */
    public function routesRegistrar(Closure $callback)
    {
        $callback($this->router);

        return $this;
    }
}
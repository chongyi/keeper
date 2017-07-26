<?php
/**
 * LifecycleTrait.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle\Illuminate;

use Dybasedev\Keeper\Http\Lifecycle\Handler;

/**
 * Trait LifecycleTrait
 *
 * @package Dybasedev\Keeper\Http\Lifecycle\Illuminate
 */
trait LifecycleTrait
{
    /**
     * @param Handler $handler
     *
     * @return RouteDispatcher
     */
    public function getRouteDispatcher(Handler $handler)
    {
        return new RouteDispatcher($handler);
    }

    /**
     * @param Handler $handler
     *
     * @return ExceptionHandler
     */
    public function getExceptionHandler(Handler $handler)
    {
        return new ExceptionHandler($handler);
    }
}
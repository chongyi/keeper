<?php
/**
 * Kernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\Container;

/**
 * Class Kernel
 *
 * HTTP 组件核心，用于提供 Swoole 或其他托管 HTTP 服务核心的组件
 * 该组件提供包括在处理整个 HTTP 请求过程的服务以及扩展支持
 *
 * @package Dybasedev\Keeper\Http
 */
class Kernel
{
    protected $services = [];

    /**
     * @var Container IoC 容器
     */
    protected $container;

    /**
     * Kernel constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        if (is_null($container)) {
            $container = new IlluminateContainer();
        }

        $this->container = $container;
    }
}
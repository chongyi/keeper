<?php
/**
 * SubProcedure.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;

use Illuminate\Contracts\Container\Container;

/**
 * Class SubProcedure
 *
 * 生命周期子过程
 *
 * @package Dybasedev\Keeper\Http\Lifecycle
 */
class SubProcedure
{
    /**
     * @var Container 容器
     */
    protected $container;

    /**
     * SubProcedure constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        // 克隆一个容器，以保证其在整个生命周期内是独立的
        $this->container = clone $container;
    }
}
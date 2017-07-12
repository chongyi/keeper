<?php
/**
 * StandardProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process\Interfaces;

/**
 * Interface StandardProcess
 *
 * 标准进程
 *
 * @package Dybasedev\Keeper\Process\Interfaces
 */
interface StandardProcess
{
    /**
     * 进程逻辑代码
     *
     * @return void
     */
    public function process();
}
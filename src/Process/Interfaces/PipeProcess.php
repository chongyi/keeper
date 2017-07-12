<?php
/**
 * PipeProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process\Interfaces;

/**
 * Interface PipeProcess
 *
 * 开启管道的进程
 *
 * @package Dybasedev\Keeper\Process\Interfaces
 */
interface PipeProcess extends StandardProcess
{
    /**
     * 是否开启重定向标准输入输出
     *
     * @return bool
     */
    public function isRedirectStdIO();

    /**
     * 管道类型
     *
     * @return bool|int
     */
    public function getPipeType();
}
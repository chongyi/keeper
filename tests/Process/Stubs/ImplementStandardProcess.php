<?php
/**
 * ImplementStandardProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Process\Stubs;

use Dybasedev\Keeper\Process\Interfaces\PipeProcess;
use Dybasedev\Keeper\Process\Process;

abstract class ImplementStandardProcess extends Process implements PipeProcess
{
    /**
     * 进程逻辑代码
     *
     * @return void
     */
    public function process()
    {
        //
    }
}
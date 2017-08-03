<?php
/**
 * ProcessExecutor.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

/**
 * Class ProcessExecutor
 *
 * 外部进程执行器
 *
 * @package Dybasedev\Keeper\Process
 */
class ProcessExecutor extends Process
{
    /**
     * @var string
     */
    protected $executable;

    /**
     * @var array|string
     */
    protected $arguments;

    /**
     * 进程逻辑代码
     *
     * @return void
     */
    public function process()
    {
        if (is_null($this->arguments)) {
            $this->arguments = [];
        }

        if (!is_array($this->arguments)) {
            $this->arguments = explode(' ', $this->arguments);
        }

        $this->getSwooleProcess()->exec($this->executable, $this->arguments);
    }

}
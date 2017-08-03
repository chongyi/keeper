<?php
/**
 * ProcessExecutor.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Dybasedev\Keeper\Process\Exceptions\RuntimeException;

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
     * @param string $executable
     *
     * @return ProcessExecutor
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;

        return $this;
    }

    /**
     * @param array|string $arguments
     *
     * @return ProcessExecutor
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * 进程逻辑代码
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function process()
    {
        if (is_null($this->arguments)) {
            $this->arguments = [];
        }

        if (!is_array($this->arguments)) {
            $this->arguments = explode(' ', $this->arguments);
        }

        if (is_null($this->executable) || !is_executable($this->executable)) {
            throw new RuntimeException();
        }

        $this->getSwooleProcess()->exec($this->executable, $this->arguments);
    }

}
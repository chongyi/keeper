<?php
/**
 * ProcessController.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Dybasedev\Keeper\Process\Exceptions\RuntimeException;
use Swoole\Process as SwProcess;

/**
 * Class ProcessController
 *
 * （子）进程控制器
 *
 * @package Dybasedev\Keeper\Process
 */
class ProcessController
{
    /**
     * @var Process
     */
    protected $masterProcess;

    /**
     * @var array
     */
    protected $registeredProcesses;

    /**
     * @var bool 终止标识
     */
    protected $terminate = false;

    /**
     * @var array|Process[]
     */
    protected $processes;

    /**
     * ProcessController constructor.
     *
     * @param Process $masterProcess
     */
    public function __construct(Process $masterProcess)
    {
        $this->masterProcess = $masterProcess;
    }

    /**
     * 注册进程类
     *
     * 该类应该继承自 Dybasedev\Keeper\Process\Process
     *
     * @param string $process 注册的进程类名
     *
     * @param array  $options
     *
     * @return $this
     */
    public function registerProcess($process, array $options = [])
    {
        $this->registeredProcesses[] = [$process, $options];

        return $this;
    }

    /**
     * 批量注册进程类
     *
     * @param \Iterator|array $processes
     */
    public function registerProcesses($processes)
    {
        foreach ($processes as $process => $options) {
            $this->registerProcess($process, $options);
        }
    }

    /**
     * 启动
     */
    public function bootstrap()
    {
        foreach ($this->registeredProcesses as list($process, $options)) {
            $this->buildProcess($this->makeProcess($process, $options));
        }
    }

    /**
     * 构建进程
     *
     * @param Process $process
     *
     * @return int
     *
     * @throws RuntimeException
     */
    private function buildProcess($process)
    {
        $process->runWithProcessController($this->masterProcess->getProcessId());

        $this->processes[$process->getProcessId()] = $process;

        return $process->getProcessId();
    }

    /**
     * @param string $processName
     * @param array  $options
     *
     * @return Process
     */
    private function makeProcess($processName, array $options)
    {
        return new $processName($options);
    }

    /**
     * 获取子进程结束事件回调
     *
     * @return \Closure
     */
    public function getChildrenProcessShutdownHandler()
    {
        return function () {
            while ($ret = SwProcess::wait(false)) {
                if ($ret) {
                    $process = clone $this->processes[$ret['pid']];
                    unset($this->processes[$ret['pid']]);

                    if (!$this->terminate && $process->isAutoReload()) {
                        $this->buildProcess($process);
                    }
                }
            }

            if (!count($this->processes)) {
                exit(0);
            }
        };
    }

    /**
     * 停止所有子进程
     */
    public function terminate()
    {
        $this->terminate = true;

        foreach ($this->processes as $pid => $process) {
            $process->kill();
        }
    }

    /**
     * 重新启动所有子进程
     *
     * 通过信号（默认 USR1）通知子进程自行重启
     */
    public function reload()
    {
        foreach ($this->processes as $processId => $process) {
            $process->reload();
        }
    }

    /**
     * 重新加载所有子进程
     *
     * 关闭并重新开启所有的子进程
     */
    public function reopen()
    {
        foreach ($this->processes as $processId => $process) {
            $process->reopen();
        }
    }
}
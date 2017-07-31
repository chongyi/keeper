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
     * @var array|Process
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
     * @var \Closure[]
     */
    protected $terminated = [];

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
     * @param string|Process $process 注册的进程实例或类名，当是一个实例时，第二个参数将被忽略
     * @param array  $options
     *
     * @return $this
     */
    public function registerProcess($process, array $options = [])
    {
        if ($process instanceof Process) {
            $this->registeredProcesses[] = $process;
        } else {
            $this->registerProcess($this->makeProcess($process, $options));
        }

        return $this;
    }

    /**
     * 批量注册进程类
     *
     * @param \Iterator|array $processes
     */
    public function registerProcesses($processes)
    {
        foreach ($processes as $describer => $body) {
            if ($body instanceof Process) {
                $this->registerProcess($body);
            } else {
                $this->registerProcess($describer, $body);
            }
        }
    }

    /**
     * 启动
     */
    public function bootstrap()
    {
        foreach ($this->registeredProcesses as $process) {
            $this->buildProcess($process);
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
    private function buildProcess(Process $process)
    {
        $process->runWithProcessController($this);

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
        $process = new $processName($options);

        if (!$process instanceof Process) {
            throw new \InvalidArgumentException();
        }

        return $process;
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
                foreach ($this->terminated as $terminated) {
                    $terminated();
                }

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

    /**
     * 注册终止回调
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function terminated(\Closure $callback)
    {
        $this->terminated[] = $callback;

        return $this;
    }

    /**
     * 获取 Master 进程 PID
     *
     * @return int
     */
    public function getMasterProcessId()
    {
        return $this->masterProcess->getProcessId();
    }
}
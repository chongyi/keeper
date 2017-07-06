<?php
/**
 * ProcessController.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

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
     * @var Process[]
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
     * @return $this
     */
    public function registerProcess($process)
    {
        $this->registeredProcesses[] = $process;

        return $this;
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
     * @param string $processName
     */
    private function buildProcess($processName)
    {
        $swProcess = new SwProcess(function (Process $swProcess) use ($processName) {
            /** @var Process $process */
            $process = new $processName($swProcess, $this->masterProcess->getProcessId());

            $process->run();
        });

        $swProcess->start();
        $this->processes[$swProcess->pid] = $processName;
    }

    /**
     * 获取子进程结束事件回调
     *
     * @return \Closure
     */
    public function getChildrenProcessShutdownHandler()
    {
        return function () {
            while (true) {
                if (count($this->processes)) {
                    $ret = SwProcess::wait(true);

                    if ($ret) {
                        $name = $this->processes[$ret['pid']];
                        unset($this->processes[$ret['pid']]);

                        if (!$this->terminate) {
                            $this->buildProcess($name);
                        }
                    }
                } else {
                    break;
                }
            }

            exit(0);
        };
    }

    /**
     * 停止所有子进程
     */
    public function terminate()
    {
        $this->terminate = true;

        foreach ($this->processes as $pid => $process) {
            SwProcess::kill($pid);
        }
    }
}
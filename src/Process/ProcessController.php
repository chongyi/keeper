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
     * @var array
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
            $this->buildProcess($process, $options);
        }
    }

    /**
     * 构建进程
     *
     * @param string $processName
     * @param array  $options
     */
    private function buildProcess($processName, array $options)
    {
        $swProcess = new SwProcess(function (SwProcess $swProcess) use ($processName) {
            /** @var Process $process */
            $process = new $processName($swProcess, $this->masterProcess->getProcessId());

            $process->run();
        });

        $swProcess->start();
        $this->processes[$swProcess->pid] = [$processName, $options];
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
                        list($name, $options) = $this->processes[$ret['pid']];
                        unset($this->processes[$ret['pid']]);

                        if (!$this->terminate && $this->isAutoReload($options)) {
                            $this->buildProcess($name, $options);
                        }
                    }
                } else {
                    break;
                }
            }

            exit(0);
        };
    }

    private function isAutoReload(array $options)
    {
        if (!isset($options['auto_reload']) || $options['auto_reload'] === true) {
            return true;
        }

        return false;
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

    public function reload()
    {
        foreach ($this->processes as $processId => list($process, $options)) {
            if (isset($options['on_reload'])) {
                if (isset($options['on_reload']['signal'])) {
                    $process->kill($options['on_reload']['signal']);
                    continue;
                } elseif ($options['on_reload'] === false) {
                    continue;
                }
            }

            if ($this->isAutoReload($options)) {
                $process->kill(SIGUSR1);
            }
        }
    }

    public function reopen()
    {
        foreach ($this->processes as $processId => list($process, $options)) {
            if (isset($options['on_reopen'])) {
                if (isset($options['on_reopen']['signal'])) {
                    $process->kill($options['on_reopen']['signal']);
                    continue;
                } elseif ($options['on_reopen'] === false) {
                    continue;
                }
            }

            if ($this->isAutoReload($options)) {
                $process->kill();
            }
        }
    }
}
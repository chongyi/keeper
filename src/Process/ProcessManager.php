<?php
/**
 * ProcessManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Dybasedev\Keeper\Process\Exceptions\OperationRejectedException;
use Dybasedev\Keeper\Process\Exceptions\SingletonException;
use Swoole\Process as SwProcess;

/**
 * Class ProcessManager
 *
 * 标准主管理进程
 *
 * @package Dybasedev\Keeper\Process
 */
abstract class ProcessManager extends Process
{
    use ProcessIdFileTrait;

    /**
     * @var bool 守护进程开关
     */
    protected $daemon = false;

    /**
     * @var ProcessController 子进程控制器
     */
    private $processController = null;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @inheritDoc
     */
    public function process()
    {
        try {
            $this->singleGuarantee();

            if ($this->daemon) {
                $this->daemon();
            }

            $this->freshProcessIdFile();

            SwProcess::signal(SIGTERM, $this->onTerminating());
            SwProcess::signal(SIGUSR1, $this->onReopen());
            SwProcess::signal(SIGUSR2, $this->onReload());

            $this->processController = new ProcessController($this);
            SwProcess::signal(SIGCHLD, $this->processController->getChildrenProcessShutdownHandler());

            $this->processController->registerProcesses($this->getChildrenProcesses());
            $this->processController->bootstrap();

            $this->running = true;
        } catch (SingletonException $e) {
            fwrite(STDERR, "Have running instance (PID: {$e->runningInstanceProcessId}). Nothing to do.\n");
            exit(1);
        }
    }

    /**
     * @return \Iterator|array
     */
    abstract protected function getChildrenProcesses();

    /**
     * 终止事件
     *
     * @return \Closure
     */
    private function onTerminating()
    {
        return function () {
            if ($this->running) {
                $this->clearProcessIdFile();
                $this->processController->terminate();
                $this->running = false;
            }
        };
    }

    /**
     * 重新加载事件
     *
     * 默认该操作会向所有子进程发起 USR1 信号，根据子进程注册参数会有差异
     *
     * @return \Closure
     */
    private function onReload()
    {
        return function () {
            $this->processController->reload();
        };
    }

    /**
     * 重新加载子进程事件
     *
     * 该操作会将所有子进程关闭并重新开启（或根据配置发起信号）
     *
     * @return \Closure
     */
    private function onReopen()
    {
        return function () {
            $this->processController->reopen();
        };
    }

    /**
     * @param bool $daemon
     *
     * @return $this
     */
    public function setDaemon($daemon)
    {
        $this->daemon = $daemon;

        return $this;
    }

    /**
     * 重启
     *
     * @param bool $force
     */
    public function restart($force = false)
    {
        try {
            $this->singleGuarantee();
            $this->clearProcessIdFile();

            if (!$force) {
                throw new OperationRejectedException();
            }

            $this->run();
        } catch (SingletonException $e) {
            $this->shutdownRunningInstance = true;
            $this->restart(true);
        } catch (OperationRejectedException $e) {
            fwrite(STDERR, "No instance can be restart.\n");
            exit(2);
        }
    }
}
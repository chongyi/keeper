<?php
/**
 * StandardMasterProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Dybasedev\Keeper\Process\Exceptions\OperationRejectedException;
use Dybasedev\Keeper\Process\Exceptions\RuntimeException;
use Swoole\Process as SwProcess;

abstract class StandardMasterProcess extends Process
{
    use ProcessIdFileTrait;

    /**
     * @var bool 守护进程开关
     */
    protected $daemon = false;

    /**
     * @var ProcessController
     */
    protected $processController = null;

    /**
     * @inheritDoc
     */
    public function process()
    {
        $this->createProcessIdFile();

        if ($this->daemon) {
            SwProcess::daemon(true, true);
        }

        SwProcess::signal(SIGTERM, $this->onTerminating());
        SwProcess::signal(SIGUSR1, $this->onReopen());
        SwProcess::signal(SIGUSR2, $this->onReload());

        $this->processController = new ProcessController($this);
        SwProcess::signal(SIGCHLD, $this->processController->getChildrenProcessShutdownHandler());

        $this->processController->registerProcesses($this->getChildrenProcesses());
        $this->processController->bootstrap();
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
            $this->clearProcessIdFile();

            $this->processController->terminate();
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
        } catch (RuntimeException $e) {
            $this->shutdownRunningInstance = true;
            $this->restart();
        }
    }
}
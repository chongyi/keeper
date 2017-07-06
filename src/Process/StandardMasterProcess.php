<?php
/**
 * StandardMasterProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

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
            flock($this->processIdFileDescriptor, LOCK_UN);
            fclose($this->processIdFileDescriptor);

            unlink($this->processIdFile);

            $this->processController->terminate();
        };
    }

    /**
     * 重新加载事件
     *
     * @return \Closure
     */
    private function onReload()
    {
        return function () {

        };
    }

    /**
     * 重新加载子进程事件
     *
     * @return \Closure
     */
    private function onReopen()
    {
        return function () {

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
}
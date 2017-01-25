<?php
/**
 * ProcessHandler.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 17:35
 */

namespace FanaticalPHP\Process;

use Swoole\Process;
use Illuminate\Contracts\Container\Container;

/**
 * Class ProcessHandler
 *
 * 托管进程管理器
 *
 * @package FanaticalPHP\Process
 */
abstract class ProcessHandler
{
    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var int
     */
    protected $processId;

    /**
     * @var Container
     */
    private $container;

    /**
     * ProcessBuilder constructor.
     *
     * @param ProcessManager $processManager
     */
    public function __construct(ProcessManager $processManager)
    {
        $this->processManager = $processManager;
        $this->container      = $processManager->getContainer();
    }

    /**
     * 获取容器
     *
     * @return Container|null
     */
    final public function getContainer()
    {
        return $this->container;
    }

    /**
     * 构建子进程
     *
     * @return \Closure
     */
    final public function buildProcess()
    {
        return function (Process $process) {
            if (!is_null($this->processManager->userId)) {
                posix_setuid($this->processManager->userId);
            }

            if (!is_null($this->processManager->groupId)) {
                posix_setgid($this->processManager->groupId);
            }
            
            $this->runProcess($process);
        };
    }

    /**
     * 执行子进程
     *
     * @return int
     */
    final public function run()
    {
        $this->process = new Process($this->buildProcess());

        return $this->processId = $this->process->start();
    }

    /**
     * @return int
     */
    final public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * 运行进程
     *
     * @param Process $process
     *
     * @return void
     */
    abstract public function runProcess(Process $process);

    /**
     * 向进程发送信号
     *
     * @param int $signal
     */
    final public function kill($signal = SIGTERM)
    {
        Process::kill($this->processId, $signal);
    }
}
<?php
/**
 * ProcessHandler.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 17:35
 */

namespace Keeper\Base\Process;

use Keeper\Base\Process\Exceptions\RuntimeException;
use Swoole\Process;
use Illuminate\Contracts\Container\Container;

/**
 * Class ProcessHandler
 *
 * 标准进程记录
 *
 * @package Keeper\Base\Process
 */
abstract class StandardProcess
{
    /**
     * @var ProcessMaster
     */
    protected $processMaster;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var int
     */
    protected $processId;

    /**
     * @param ProcessMaster $processMaster
     *
     * @return $this
     */
    public function setProcessMaster(ProcessMaster $processMaster)
    {
        $this->processMaster = $processMaster;

        return $this;
    }

    /**
     * 构建子进程
     *
     * @return \Closure
     */
    final public function buildProcess()
    {
        return function (Process $process) {
            try {
                if (!is_null($this->processMaster->userId)) {
                    posix_setuid($this->processMaster->userId);
                }

                if (!is_null($this->processMaster->groupId)) {
                    posix_setgid($this->processMaster->groupId);
                }

                $this->runProcess($process);
            } catch (\Exception $exception) {
                throw new RuntimeException($this, $exception);
            } catch (\Throwable $exception) {
                throw new RuntimeException($this, $exception);
            }
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
     * @return Process
     */
    final public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return int
     */
    final public function getProcessPipe()
    {
        return $this->process->pipe;
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
    public function kill($signal = SIGTERM)
    {
        Process::kill($this->processId, $signal);
    }

    /**
     * 停止
     */
    final public function stop()
    {
        $this->kill();
    }
}
<?php
/**
 * Process.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Dybasedev\Keeper\Process\Exceptions\OperationRejectedException;
use Dybasedev\Keeper\Process\Interfaces\PipeProcess;
use Dybasedev\Keeper\Process\Interfaces\StandardProcess;
use Swoole\Process as SwProcess;

/**
 * Class Process
 *
 * 标准进程类
 *
 * @package Dybasedev\Keeper\Process
 */
abstract class Process implements StandardProcess
{
    /**
     * @var int Current process id.
     */
    protected $processId;

    /**
     * @var SwProcess
     */
    protected $swooleProcess;

    /**
     * @var int
     */
    protected $masterId;

    /**
     * @var int
     */
    protected $ownerGroupId = null;

    /**
     * @var int
     */
    protected $ownerUserId = null;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    public $runtime;

    /**
     * @var bool
     */
    protected $withProcessController = false;

    /**
     * @var resource
     */
    public $pipe;

    /**
     * Process constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param int $ownerGroupId
     *
     * @return Process
     */
    public function setOwnerGroupId($ownerGroupId)
    {
        $this->ownerGroupId = $ownerGroupId;

        return $this;
    }

    /**
     * @param int $ownerUserId
     *
     * @return Process
     */
    public function setOwnerUserId($ownerUserId)
    {
        $this->ownerUserId = $ownerUserId;

        return $this;
    }

    /**
     * 启动进程
     *
     * @param int $masterId
     *
     * @return $this
     */
    public function run($masterId = null)
    {
        $this->masterId = $masterId;

        if ($this->isTemporaryAutoReload()) {
            $this->clearTemporaryAutoLoadStatus();
        }

        $swooleProcessInstance = $this->buildSwooleProcessInstance(
            $this->generateSwooleProcessCallback()
        );

        $this->processId     = $swooleProcessInstance->start();
        $this->pipe          = $swooleProcessInstance->pipe;
        $this->swooleProcess = $swooleProcessInstance;

        return $this;
    }

    /**
     * 通过控制器启动
     *
     * @param int $masterId
     *
     * @return $this
     */
    public function runWithProcessController($masterId)
    {
        $this->withProcessController = true;

        return $this->run($masterId);
    }

    /**
     * 清理临时自动重载状态
     */
    public function clearTemporaryAutoLoadStatus()
    {
        unset($this->runtime['temp_auto_reload']);
    }

    /**
     * 变更所有者
     */
    private function changeCurrentOwner()
    {
        if (!is_null($this->ownerUserId) && $this->ownerUserId != posix_getuid()) {
            posix_setuid($this->ownerUserId);
        }

        if (!is_null($this->ownerGroupId) && $this->ownerGroupId != posix_getgid()) {
            posix_setgid($this->ownerGroupId);
        }
    }

    /**
     * 是否自动重新加载
     *
     * @return bool
     */
    public function isAutoReload()
    {
        if (!isset($this->options['auto_reload']) ||
            $this->options['auto_reload'] === true ||
            $this->isTemporaryAutoReload()
        ) {
            return true;
        }

        return false;
    }

    /**
     * 是否为临时的允许重新加载
     *
     * @return bool
     */
    public function isTemporaryAutoReload()
    {
        if (isset($this->runtime['temp_auto_reload']) && $this->runtime['temp_auto_reload']) {
            return true;
        }

        return false;
    }

    /**
     * 获取该进程 ID
     *
     * @return int
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * 获取设置项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 获取该进程 Swoole\Process 实例
     *
     * @return SwProcess
     */
    public function getSwooleProcess()
    {
        return $this->swooleProcess;
    }

    /**
     * 获取该进程主（父）进程 ID
     *
     * @return int
     */
    public function getMasterId()
    {
        return $this->masterId;
    }

    protected function daemon()
    {
        SwProcess::daemon(true, true);

        $this->processId = posix_getpid();
    }

    public function kill($signal = SIGTERM)
    {
        SwProcess::kill($this->getProcessId(), $signal);
    }

    /**
     * 进程重启
     */
    public function reload()
    {
        if ($this->withProcessController) {
            if (isset($this->options['on_reload'])) {
                if (isset($this->options['on_reload']['signal'])) {
                    $this->kill($this->options['on_reload']['signal']);

                    return;
                } elseif ($this->options['on_reload'] === false) {
                    return;
                }
            }

            $this->kill(SIGUSR1);
        }
    }

    /**
     * 进程重载
     */
    public function reopen()
    {
        if ($this->withProcessController) {
            if (isset($this->options['on_reopen'])) {
                if (isset($this->options['on_reopen']['signal'])) {
                    $this->kill($this->options['on_reopen']['signal']);

                    return;
                } elseif ($this->options['on_reopen'] === false) {
                    return;
                }
            }

            if (!$this->isAutoReload()) {
                $this->runtime['temp_auto_reload'] = true;
                $this->kill();
            }
        }
    }

    public function __clone()
    {
        $this->swooleProcess         = null;
        $this->processId             = null;
        $this->withProcessController = false;
    }

    /**
     * 生成 Swoole 进程回调
     *
     * @return \Closure
     */
    protected function generateSwooleProcessCallback()
    {
        return function (SwProcess $process) {
            $this->swooleProcess = $process;
            $this->processId     = $process->pid;

            // 对于超级管理员用户而言，该操作才会生效。
            // 该操作调用了 setuid 和 setgid 作改变当前进程的实际用户 ID。
            $this->changeCurrentOwner();

            // 调用实际进程处理逻辑
            $this->process();
        };
    }

    /**
     * 构造 Swoole Process 实例
     *
     * @param callable $processCallback
     *
     * @return SwProcess
     */
    public function buildSwooleProcessInstance($processCallback)
    {
        if ($this instanceof PipeProcess) {
            $process = new SwProcess($processCallback, $this->isRedirectStdIO(), $this->getPipeType());
        } else {
            $process = new SwProcess($processCallback);
        }

        return $process;
    }
}
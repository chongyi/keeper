<?php
/**
 * Process.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Swoole\Process as SwProcess;

/**
 * Class Process
 *
 * 标准进程类
 *
 * @package Dybasedev\Keeper\Process
 */
abstract class Process
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
     * @var bool
     */
    protected $withProcessController = false;

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
     * @return mixed
     */
    abstract public function process();

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
     */
    public function run()
    {
        // 对于超级管理员用户而言，该操作才会生效。
        // 该操作调用了 setuid 和 setgid 作改变当前进程的实际用户 ID。
        $this->changeCurrentOwner();

        $this->processId = posix_getpid();

        // 调用实际进程处理逻辑
        $this->process();
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

        if ($this->isTemporaryAutoReload()) {
            unset($this->options['temp_auto_reload']);
        }

        $this->swooleProcess = new SwProcess(function (SwProcess $process) use ($masterId) {
            $this->swooleProcess = $process;
            $this->masterId      = $masterId;

            $this->run();
        });

        $this->processId = $this->swooleProcess->start();
        $this->masterId  = $masterId;

        return $this;
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
        if (isset($this->options['temp_auto_reload']) && $this->options['temp_auto_reload']) {
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
                $this->options['temp_auto_reload'] = true;
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
}
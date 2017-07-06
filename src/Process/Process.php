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
     * Process constructor.
     *
     * @param SwProcess $swooleProcess
     * @param int       $masterId
     */
    public function __construct(SwProcess $swooleProcess = null, $masterId = null)
    {
        // 若该进程启动是通过 swoole process 开启则应该收到此参数
        // 该值决定了其是否为一个子进程
        // master id 和 swoole process 两个值原则上应该同时提供，但此处不做限制
        $this->SwProcess = $swooleProcess;
        $this->masterId  = $masterId;

        $this->setOwnerUserId(posix_getuid())->setOwnerGroupId(posix_getgid());
        $this->processId = posix_getpid();
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


    public function run()
    {
        // 对于超级管理员用户而言，该操作才会生效。
        // 该操作调用了 setuid 和 setgid 作改变当前进程的实际用户 ID。
        $this->changeCurrentOwner();

        // 调用实际进程处理逻辑
        $this->process();
    }

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
     * 获取该进程 ID
     *
     * @return int
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * 获取该进程 Swoole\Process 实例
     *
     * @return SwProcess
     */
    public function getSwooleProcess()
    {
        return $this->SwProcess;
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
}
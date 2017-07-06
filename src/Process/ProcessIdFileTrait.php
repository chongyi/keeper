<?php
/**
 * ProcessIdFileTrait.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process;

use Swoole\Process;
use Dybasedev\Keeper\Process\Exceptions\RuntimeException;

/**
 * Trait ProcessIdFileTrait
 *
 * @package Dybasedev\Keeper\Process
 */
trait ProcessIdFileTrait
{
    /**
     * @var string
     */
    protected $processIdFile;

    /**
     * @var resource
     */
    protected $processIdFileDescriptor = null;

    /**
     * @var bool
     */
    protected $shutdownRunningInstance = false;

    /**
     * 创建 PID 文件
     */
    private function createProcessIdFile()
    {
        $this->singleGuarantee();

        ftruncate($this->processIdFileDescriptor, 0);
        fwrite($this->processIdFileDescriptor, $this->processId);
    }

    /**
     * 检查以保证进程单例
     *
     * @throws RuntimeException
     */
    private function singleGuarantee()
    {
        if ($this->hasProcessIdFile()) {
            $runningProcessId = $this->getProcessIdFromFile();

            if ($runningProcessId !== false) {
                if (!$this->shutdownRunningInstance) {
                    throw new RuntimeException();
                }

                Process::kill($runningProcessId);
                sleep(3);

                $this->singleGuarantee();
            }
        } else {
            touch($this->processIdFile);
            $this->getProcessIdFromFile();
        }
    }

    /**
     * 从文件获取 PID
     *
     * 若该 PID 文件未有进程加锁则认为其值不可取，返回 false。
     *
     * @return bool|int
     *
     * @throws RuntimeException
     */
    private function getProcessIdFromFile()
    {
        $fileDescriptor = fopen($this->processIdFile, 'r+');

        if (!$fileDescriptor) {
            throw new RuntimeException();
        }

        if (flock($fileDescriptor, LOCK_EX | LOCK_NB)) {
            // 同步变更 PID 文件描述符至类属性
            $this->processIdFileDescriptor = $fileDescriptor;
            return false;
        }

        $processId = fread($fileDescriptor, 64);
        fclose($fileDescriptor);

        return (int)$processId;
    }

    /**
     * 是否存在 PID 文件
     *
     * @return bool
     */
    private function hasProcessIdFile()
    {
        $processIdFilePath = dirname($this->processIdFile);

        if (!is_dir($processIdFilePath)) {
            mkdir($processIdFilePath, 0644, true);

            return false;
        } elseif (!is_file($this->processIdFile)) {
            return false;
        }

        return true;
    }

    /**
     * 设置 PID 文件位置
     *
     * @param string $processIdFile
     *
     * @return $this
     */
    public function setProcessIdFile($processIdFile)
    {
        $this->processIdFile = $processIdFile;

        return $this;
    }
}
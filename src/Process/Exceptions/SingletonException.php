<?php
/**
 * SingletonException.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Process\Exceptions;

/**
 * Class SingletonException
 *
 * 单例异常
 *
 * 作为单例运行时若发现已存在运行的实例则抛出该异常
 *
 * @package Dybasedev\Keeper\Process\Exceptions
 */
class SingletonException extends RuntimeException
{
    /**
     * @var int
     */
    public $runningInstanceProcessId;

    /**
     * @return int
     */
    public function getRunningInstanceProcessId()
    {
        return $this->runningInstanceProcessId;
    }

    /**
     * @param int $runningInstanceProcessId
     *
     * @return SingletonException
     */
    public function setRunningInstanceProcessId($runningInstanceProcessId)
    {
        $this->runningInstanceProcessId = $runningInstanceProcessId;

        return $this;
    }
}
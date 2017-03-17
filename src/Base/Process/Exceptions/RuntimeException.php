<?php
/**
 * RuntimeException.php
 *
 * Creator:    chongyi
 * Created at: 2017/03/17 10:48
 */

namespace Keeper\Base\Process\Exceptions;

use Exception;
use Keeper\Base\Process\StandardProcess;

/**
 * Class RuntimeException
 *
 * 进程运行时异常
 *
 * @package Keeper\Base\Process\Exceptions
 */
class RuntimeException extends Exception
{
    protected $process;

    /**
     * @inheritDoc
     */
    public function __construct(StandardProcess $process, Exception $previous)
    {
        $this->process = $process;

        $message = "Process({$process->getProcessId()}) exception: {$previous->getMessage()}";
        $code    = $previous->getCode();

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return StandardProcess
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return int
     */
    public function getProcessId()
    {
        return $this->process->getProcessId();
    }
}
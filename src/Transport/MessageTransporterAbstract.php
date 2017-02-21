<?php
/**
 * MessageTransporterAbstract.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:47
 */

namespace Keeper\Transport;

use Closure;

/**
 * Class MessageTransporter
 *
 * @package Keeper\Transport
 */
abstract class MessageTransporterAbstract
{
    /**
     * @var Closure
     */
    protected $transporter;

    /**
     * @var string
     */
    protected $compiled;

    /**
     * @var Message
     */
    protected $message;


    /**
     * @param Closure $callback
     *
     * @return $this
     */
    public function transporter(Closure $callback)
    {
        $this->transporter = $callback;

        return $this;
    }

    /**
     * @return string
     */
    abstract public function getCompiled();

    /**
     * 获取寄送目标
     *
     * @return mixed
     */
    abstract public function getTarget();


    /**
     * @return mixed
     */
    public function send()
    {
        $compiled = $this->getCompiled();

        return call_user_func_array($this->transporter, [$this->getTarget(), $compiled]);
    }
}
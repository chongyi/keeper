<?php
/**
 * MessageTransporterAbstract.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:47
 */

namespace FanaticalPHP\Transport;

use Closure;

/**
 * Class MessageTransporter
 *
 * @packageFanaticalPHP\Transport
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
     * @return mixed
     */
    public function send()
    {
        $compiled = $this->getCompiled();

        return call_user_func_array($this->transporter, [$compiled]);
    }
}
<?php
/**
 * AbstractMessageReceiver.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:52
 */

namespace FanaticalPHP\Transport;

use FanaticalPHP\Transport\Exceptions\DataResolveException;

/**
 * Class MessageReceiver
 *
 * @package FanaticalPHP\Transport
 */
abstract class MessageReceiverAbstract
{
    /**
     * @var string 解析前的原始数据
     */
    protected $origin;

    /**
     * @var Message 解析结果
     */
    protected $resolved;

    /**
     * @return Message
     *
     * @throws DataResolveException
     */
    abstract public function getResolved();
}
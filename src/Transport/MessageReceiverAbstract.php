<?php
/**
 * AbstractMessageReceiver.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:52
 */

namespace Keeper\Transport;

use Keeper\Transport\Exceptions\DataResolveException;

/**
 * Class MessageReceiver
 *
 * 消息接收器
 *
 * @package Keeper\Transport
 */
interface MessageReceiverAbstract
{
    /**
     * 获取解析的消息
     *
     * @param mixed $origin
     *
     * @return Message
     *
     * @throws DataResolveException
     */
    public function getResolved($origin);
}
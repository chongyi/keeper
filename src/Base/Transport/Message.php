<?php
/**
 * Message.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:53
 */

namespace Keeper\Base\Transport;

/**
 * Interface Message
 *
 * 消息接口
 *
 * @package Keeper\Base\Transport
 */
interface Message
{
    /**
     * 解析消息
     *
     * @param string $messageChunk
     *
     * @return Message
     */
    public function resolve($messageChunk);

    /**
     * 打包编译消息
     *
     * @return string
     */
    public function compile();
}
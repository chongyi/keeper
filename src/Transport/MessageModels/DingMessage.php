<?php
/**
 * DingMessage.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:40
 */

namespace FanaticalPHP\Transport\MessageModels;


use FanaticalPHP\Transport\Message;

class DingMessage implements Message
{
    const MESSAGE_CHUNK_PACK_FORMAT_STRING = 'CCn';

    /**
     * 数据分为两段：基准段，消息段。该部分为消息段头部处理格式。
     * 消息段头部数据结构如下：
     *
     * <pre>
     * struct messageChunkHead {
     *     unsigned char type;
     *     unsigned char status;
     *     unsigned short referenceCode;
     * }
     * </pre>
     *
     * 共计 4 个字节的长度。
     */
    const MESSAGE_CHUNK_UNPACK_FORMAT_STRING = 'Ctype/Cstatus/nreferenceCode';

    /**
     * @var int $type 0 表示简单通讯测试，仅在开发阶段使用，常规连接中往往是大于 0 的数字
     */
    public $type;

    /**
     * @var int $status 0 表示正常状态， 大于 0 表示存在其他可能，需要参考 referenceCode 做出具体处理
     */
    public $status;

    /**
     * @var int
     */
    public $referenceCode;

    /**
     * @param string $messageChunk
     *
     * @return Message
     */
    public function resolve($messageChunk)
    {
        $resolved = unpack(static::MESSAGE_CHUNK_UNPACK_FORMAT_STRING, $messageChunk);

        list($this->type, $this->status, $this->referenceCode) = array_values($resolved);

        return $this;
    }

    /**
     * 打包编译消息
     *
     * @return string
     */
    public function compile()
    {
        return pack(static::MESSAGE_CHUNK_PACK_FORMAT_STRING, $this->type, $this->status, $this->referenceCode);
    }

    /**
     * @param int $type
     *
     * @return DingMessage
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $status
     *
     * @return DingMessage
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $referenceCode
     *
     * @return DingMessage
     */
    public function setReferenceCode($referenceCode)
    {
        $this->referenceCode = $referenceCode;
        return $this;
    }


}
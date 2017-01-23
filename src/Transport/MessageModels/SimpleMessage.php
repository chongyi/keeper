<?php
/**
 * SimpleMessage.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:40
 */

namespace FanaticalPHP\Transport\MessageModels;


use FanaticalPHP\Transport\Exceptions\DataResolveException;
use FanaticalPHP\Transport\Message;

class SimpleMessage implements Message
{
    const MESSAGE_CHUNK_PACK_FORMAT_STRING = 'CCnN';

    /**
     * 数据分为两段：基准段，消息段。该部分为消息段头部处理格式。
     * 消息段头部数据结构如下：
     *
     * <pre>
     * struct messageChunkHead {
     *     unsigned char type;
     *     unsigned char status;
     *     unsigned short code;
     *     unsigned int   length;
     * }
     * </pre>
     *
     * 共计 8 个字节的长度。
     */
    const MESSAGE_CHUNK_UNPACK_FORMAT_STRING = 'Ctype/Cstatus/ncode/Nlength';

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
    public $code;

    /**
     * @var int 消息实体长度（不包含消息头）
     */
    public $length;

    /**
     * @var string 消息实体
     */
    public $entity;

    /**
     * 解析消息
     *
     * @param string $messageChunk
     *
     * @return Message
     * @throws DataResolveException
     */
    public function resolve($messageChunk)
    {
        $resolved = unpack(static::MESSAGE_CHUNK_UNPACK_FORMAT_STRING, substr($messageChunk, 0, 8));

        list($this->type, $this->status, $this->code, $this->length) = array_values($resolved);

        $entity = substr($messageChunk, 8);
        $length = strlen($entity);

        if ($length < $this->length || $length > $this->length) {
            throw new DataResolveException('out of length.');
        }

        $this->entity = $entity;

        return $this;
    }

    /**
     * 打包编译消息
     *
     * @return string
     */
    public function compile()
    {
        $this->length     = strlen($this->entity);
        $messageHeadChunk = pack(static::MESSAGE_CHUNK_PACK_FORMAT_STRING, $this->type, $this->status, $this->code,
            $this->length);

        return $messageHeadChunk . $this->entity;
    }

    /**
     * @param int $type
     *
     * @return SimpleMessage
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int $status
     *
     * @return SimpleMessage
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param int $code
     *
     * @return SimpleMessage
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param string $entity
     *
     * @return SimpleMessage
     */
    public function setEntity($entity)
    {
        if (is_array($entity)) {
            $entity = serialize($entity);
        }

        $this->entity = $entity;

        return $this;
    }
}
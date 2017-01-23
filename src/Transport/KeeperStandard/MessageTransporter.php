<?php
/**
 * MessageTransporter.php
 *
 * Creator:    chongyi
 * Created at: 2017/01/13 10:54
 */

namespace OverpoweredService\KeeperPlus\Transport\KeeperStandard;

use FanaticalPHP\Transport\Message;
use FanaticalPHP\Transport\MessageObjectMap;
use FanaticalPHP\Transport\MessageTransporterAbstract;

class MessageTransporter extends MessageTransporterAbstract
{
    /**
     * 数据分为两段：基准段，消息段。消息段数据解析方式，以基准段为准。因此这一部分主要的工作就是处理基准段数据。
     * 基准段数据结构如下：
     *
     * <pre>
     * struct baseChunk {
     *     unsigned char protocolType;
     *     unsigned char originType;
     *     unsigned char targetType;
     *     unsigned char messageType;
     * }
     * </pre>
     *
     * 共计 4 个字节的长度。
     */
    const BASE_CHUNK_PACK_FORMAT_STRING = 'CCCC';
    /**
     * @var int
     */
    protected $originType = 0;
    /**
     * @var int
     */
    protected $targetType = 0;
    /**
     * @var int $protocolType 目前仅支持 1
     */
    protected $protocolType = 1;


    /**
     * MessageTransporter constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getOriginType()
    {
        return $this->originType;
    }

    /**
     * @param int $originType
     *
     * @return MessageTransporterAbstract
     */
    public function setOriginType($originType)
    {
        if ($originType > 255 || $originType < 0) {
            throw new \InvalidArgumentException('out of limit.');
        }

        $this->originType = $originType;

        return $this;
    }

    /**
     * @return int
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param int $targetType
     *
     * @return MessageTransporterAbstract
     */
    public function setTargetType($targetType)
    {
        if ($targetType > 255 || $targetType < 0) {
            throw new \InvalidArgumentException('out of limit.');
        }

        $this->targetType = $targetType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompiled()
    {
        if (!is_null($this->compiled)) {
            return $this->compiled;
        }

        $messageType = MessageObjectMap::getId(get_class($this->message));
        $compiledBaseChunk
                     = pack(static::BASE_CHUNK_PACK_FORMAT_STRING, $this->protocolType, $this->originType,
            $this->targetType,
            $messageType);

        return $this->compiled = $compiledBaseChunk . $this->message->compile();
    }
}
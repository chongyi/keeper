<?php
/**
 * MessageReceiver.php
 *
 * Creator:    chongyi
 * Created at: 2017/01/13 10:47
 */

namespace FanaticalPHP\Transport\KeeperStandard;

use FanaticalPHP\Transport\Message;
use FanaticalPHP\Transport\MessageObjectIndex;
use FanaticalPHP\Transport\Exceptions\DataResolveException;
use FanaticalPHP\Transport\MessageReceiverAbstract;

class MessageReceiver extends MessageReceiverAbstract
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
    const BASE_CHUNK_UNPACK_FORMAT_STRING = 'CprotocolType/CoriginType/CtargetType/CmessageType';

    /**
     * @var array 基准块解析结果
     */
    protected $base;

    /**
     * MessageReceiver constructor.
     *
     * @param string $origin
     */
    public function __construct($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return Message
     *
     * @throws DataResolveException
     */
    public function getResolved()
    {
        if (!is_null($this->resolved)) {
            return $this->resolved;
        }

        // 取开头 4 个字节
        $baseChunkOriginData = substr($this->origin, 0, 4);

        // 解包操作
        $this->base = $baseChunkResolvedData = unpack(static::BASE_CHUNK_UNPACK_FORMAT_STRING, $baseChunkOriginData);

        // 目前版本就只支持 1
        if (!isset($baseChunkResolvedData['protocolType']) || $baseChunkResolvedData['protocolType'] != 1) {
            throw new DataResolveException('Unknown protocol type or version.', 1);
        }

        // 目前先不对来源和目标进行判断，当前也没有对于两者做区分的必要
        // 因此直接判断消息类型，然后做对应的后续解析处理
        if (!isset($baseChunkResolvedData['messageType']) ||
            is_null($messageType = MessageObjectIndex::find($baseChunkResolvedData['messageType']))
        ) {
            throw new DataResolveException('Unknown message type.', 4);
        }

        /** @var Message $message */
        $message = new $messageType;

        return $this->resolved = $message->resolve(substr($this->origin, 4));
    }
}
<?php
/**
 * JsonMessage.php
 *
 * Creator:    chongyi
 * Created at: 2017/01/13 11:04
 */

namespace FanaticalPHP\Transport\MessageModels;


use FanaticalPHP\Transport\Exceptions\DataResolveException;
use FanaticalPHP\Transport\Message;

class JsonMessage implements Message
{
    protected $body;

    protected $version = 1;

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return JsonMessage
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return JsonMessage
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }


    /**
     * 解析消息
     *
     * @param string $messageChunk
     *
     * @throws DataResolveException
     * @return Message
     */
    public function resolve($messageChunk)
    {
        $data = json_decode($messageChunk, true);

        if (!isset($data['body']) || !isset($data['v'])) {
            throw new DataResolveException();
        }

        $this->setBody($data['body'])->setVersion($data['v']);

        return $this;
    }

    /**
     * 打包编译消息
     *
     * @return string
     */
    public function compile()
    {
        return json_encode(['v' => $this->version, 'body' => $this->body]);
    }

}
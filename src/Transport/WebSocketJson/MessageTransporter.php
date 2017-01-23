<?php
/**
 * MessageTransporter.php
 *
 * Creator:    chongyi
 * Created at: 2017/01/13 11:00
 */

namespace OverpoweredService\KeeperPlus\Transport\WebSocketJson;

use FanaticalPHP\Transport\MessageModels\JsonMessage;
use FanaticalPHP\Transport\MessageTransporterAbstract;

class MessageTransporter extends MessageTransporterAbstract
{
    protected $target = null;

    /**
     * MessageTransporter constructor.
     *
     * @param JsonMessage $message
     */
    public function __construct(JsonMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCompiled()
    {
        return $this->message->compile();
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     *
     * @return MessageTransporter
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }



}
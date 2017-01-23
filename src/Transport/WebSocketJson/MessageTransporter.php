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

}
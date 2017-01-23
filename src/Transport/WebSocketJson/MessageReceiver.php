<?php
/**
 * MessageReceiver.php
 *
 * Creator:    chongyi
 * Created at: 2017/01/13 11:00
 */

namespace FanaticalPHP\Transport\WebSocketJson;


use FanaticalPHP\Transport\Exceptions\DataResolveException;
use FanaticalPHP\Transport\Message;
use FanaticalPHP\Transport\MessageModels\JsonMessage;
use FanaticalPHP\Transport\MessageReceiverAbstract;

class MessageReceiver extends MessageReceiverAbstract
{
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

        return (new JsonMessage())->resolve($this->origin);
    }

}
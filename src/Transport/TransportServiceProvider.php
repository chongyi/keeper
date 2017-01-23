<?php
/**
 * TransportServiceProvider.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:59
 */

namespace FanaticalPHP\Transport;


use Illuminate\Support\ServiceProvider;
use FanaticalPHP\Transport\MessageModels\DingMessage;
use FanaticalPHP\Transport\MessageModels\SimpleMessage;

class TransportServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        MessageObjectMap::register(1, DingMessage::class);
        MessageObjectMap::register(2, SimpleMessage::class);
    }

}
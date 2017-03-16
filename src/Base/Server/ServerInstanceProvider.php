<?php
/**
 * ServerInstanceProvider.php
 *
 * Creator:    chongyi
 * Created at: 2017/03/16 18:06
 */

namespace Keeper\Base\Server;

use Swoole\Server;

/**
 * Interface ServerInstanceProvider
 *
 * 服务器实例提供者
 *
 * @package Keeper\Base\Server
 */
interface ServerInstanceProvider
{
    /**
     * 创建 Server 实例
     *
     * @return Server
     */
    public function makeServerInstance();
}
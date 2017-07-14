<?php
/**
 * ServerProcess.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Dybasedev\Keeper\Process\Process;
use Swoole\Http\Server;

/**
 * Class ServerProcess
 *
 * HTTP 服务器进程
 *
 * @package Dybasedev\Keeper\Http
 */
abstract class ServerProcess extends Process
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * 该值与 $server 的区别在于其是在 worker start 后获取的
     *
     * @var Server
     */
    protected $actualServer;

    /**
     * @var bool
     */
    protected $worker = false;

    /**
     * @var int
     */
    protected $workerId;

    /**
     * @inheritDoc
     */
    public function process()
    {
        $this->server = $this->createSwServer();

        $this->server->on('start', $this->onStart());
        $this->server->on('managerStart', $this->onManagerStart());
        $this->server->on('workerStart', $this->onWorkerStart());
        $this->server->on('request', $this->onRequest());

        $this->server->start();
    }

    /**
     * @return \Closure
     */
    protected function onStart()
    {
        return function () {
            $this->setProcessNameSuffix('', false);
        };
    }

    /**
     * @return \Closure
     */
    protected function onWorkerStart()
    {
        return function (Server $server, $workerId) {
            $this->actualServer = $server;
            $this->worker       = true;
            $this->workerId     = $workerId;
            $this->setProcessNameSuffix('worker#' . $workerId);
        };
    }

    /**
     * @return \Closure
     */
    protected function onManagerStart()
    {
        return function () {
            $this->setProcessNameSuffix('manager');
        };
    }

    /**
     * @return \Closure
     */
    abstract function onRequest();

    /**
     * 创建 Swoole Server 实例
     *
     * @return Server
     */
    protected function createSwServer()
    {
        $server = new Server($this->options['host'], $this->options['port']);
        $server->set([
            'worker_num' => isset($this->options['worker']) ? $this->options['worker'] : 4,
        ]);

        return $server;
    }

    /**
     * 获取进程名设置
     *
     * @return string|null
     */
    private function getProcessNamePrefix()
    {
        if (isset($this->options['process_name'])) {
            return $this->options['process_name'];
        }

        return null;
    }

    /**
     * 设置进程名后缀
     *
     * @param      $suffix
     * @param bool $space
     */
    private function setProcessNameSuffix($suffix, $space = true)
    {
        if ($processName = $this->getProcessNamePrefix()) {
            if ($space) {
                $suffix = ' ' . trim($suffix);
            }

            cli_set_process_title($processName . $suffix);
        }
    }
}
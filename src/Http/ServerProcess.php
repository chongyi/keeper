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
use Swoole\Http\Request;
use Swoole\Http\Response;

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

        $this->server->on('start', $this->serverStartCallback());
        $this->server->on('managerStart', $this->serverManagerStartCallback());
        $this->server->on('workerStart', $this->workerStartCallback());
        $this->server->on('request', $this->httpRequestCallback());

        $this->server->start();
    }

    /**
     * @return \Closure
     */
    protected function serverStartCallback()
    {
        return function () {
            $this->setProcessNameSuffix('', false);
        };
    }

    /**
     * @return \Closure
     */
    protected function workerStartCallback()
    {
        return function (Server $server, $workerId) {
            $this->actualServer = $server;
            $this->worker       = true;
            $this->workerId     = $workerId;
            $this->setProcessNameSuffix('worker#' . $workerId);

            $this->onWorkerStart();
        };
    }

    /**
     * @return \Closure
     */
    protected function serverManagerStartCallback()
    {
        return function () {
            $this->setProcessNameSuffix('manager');
        };
    }

    /**
     * @return \Closure
     */
    protected function httpRequestCallback()
    {
        return function (Request $request, Response $response) {
            $this->onRequest($request, $response);
        };
    }

    /**
     * HTTP 请求事件
     *
     * @param Request $request
     * @param Response $response
     */
    abstract function onRequest(Request $request, Response $response);

    /**
     * Worker 启动事件
     */
    abstract function onWorkerStart();

    /**
     * 获取 Worker 启动后的 Server 实例
     *
     * @return Server
     */
    public function getActualServer()
    {
        return $this->actualServer;
    }

    /**
     * 判断是否是运行于 Worker 中
     *
     * @return bool
     */
    public function isWorker()
    {
        return $this->worker;
    }

    /**
     * 获取 Worker ID
     *
     * @return int
     */
    public function getWorkerId()
    {
        return $this->workerId;
    }

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
<?php
/**
 * HostingProcess.php
 *
 * Creator:    chongyi
 * Created at: 2017/03/16 18:03
 */

namespace Keeper\Base\Server;

use Illuminate\Contracts\Container\Container;
use Keeper\Base\Process\StandardProcess;
use Swoole\Process;
use Swoole\Server;

/**
 * Class HostingProcess
 *
 * @package Keeper\Base\Server
 */
class HostingProcess extends StandardProcess
{
    /**
     * @var ServerInstanceProvider
     */
    protected $provider;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Server
     */
    protected $server;

    /**
     * HostingProcess constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|\Closure|$provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        if (is_string($provider) || $provider instanceof \Closure) {
            $this->provider = $this->container->make($provider);

            return $this;
        }

        $this->provider = $provider;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runProcess(Process $process)
    {
        $this->server = $this->provider->makeServerInstance();

        $this->server->start();
    }
}
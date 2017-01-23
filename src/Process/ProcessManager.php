<?php
/**
 * ProcessManager.php
 *
 * Creator:         chongyi
 * Create Datetime: 2017/1/23 22:04
 */

namespace FanaticalPHP\Process;

use Swoole\Process;
use Illuminate\Contracts\Container\Container;
use Illuminate\Container\Container as laravelContainer;

/**
 * Class ProcessManager
 *
 * 主进程管理器
 *
 * @package FanaticalPHP\Process
 */
class ProcessManager
{
    /**
     * @var int
     */
    public $processId;

    /**
     * @var string
     */
    public $processName;

    /**
     * @var string
     */
    public $pidFile;

    /**
     * @var resource
     */
    protected $pidFileHandle;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var boolean
     */
    protected $daemon = false;

    /**
     * @var ProcessHandler[]
     */
    protected $childrenProcesses = [];

    /**
     * @var string[]
     */
    protected $bootstrapLoaders = [];

    /**
     * @var string
     */
    public $user;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $group;

    /**
     * @var int
     */
    public $groupId;

    /**
     * @var int 最后一次收到的信号
     */
    protected $signal;

    /**
     * ProcessManager constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = is_null($container) ? new LaravelContainer : $container;
    }

    /**
     * 开启守护进程
     *
     * @param bool $daemon 默认为 false
     *
     * @return $this
     */
    public function daemon($daemon = false)
    {
        $this->daemon = $daemon;

        return $this;
    }

    /**
     * 设置 PID 文件路径
     *
     * @param string $path
     *
     * @return $this
     */
    public function pidFile($path)
    {
        $this->pidFile = $path;

        return $this;
    }

    /**
     * @param string $user
     *
     * @return ProcessManager
     */
    public function user($user)
    {
        $this->user   = $user;
        $this->userId = posix_getpwnam($user)['uid'];

        return $this;
    }

    /**
     * @param string $group
     *
     * @return ProcessManager
     */
    public function group($group)
    {
        $this->group   = $group;
        $this->groupId = posix_getgrnam($group)['gid'];

        return $this;
    }

    /**
     * 运行
     */
    public function run()
    {
        if (!is_dir($path = dirname($this->pidFile))) {
            @mkdir($path, 0744, true);
        }

        if (!is_file($this->pidFile)) {
            touch($this->pidFile);
        }

        $this->pidFileHandle = fopen($this->pidFile, 'r+');

        if (!$this->pidFileHandle) {
            fwrite(STDERR, "Create PID file failed.\n");
            exit(1);
        }

        if (!flock($this->pidFileHandle, LOCK_EX | LOCK_NB)) {
            $fd  = fopen($this->pidFile, 'r');
            $pid = fread($fd, 64);

            print "Have running instance (PID: $pid). Nothing to do.\n";
            fclose($fd);
            exit(2);
        }

        if ($this->daemon) {
            Process::daemon(true, true);
        }

        ftruncate($this->pidFileHandle, 0);
        fwrite($this->pidFileHandle, $this->processId = getmypid());

        $this->handlerManagerProcess();
    }

    /**
     * 主进程管理器注册
     */
    protected function handlerManagerProcess()
    {
        Process::signal(SIGTERM, $this->onTerminating());
        Process::signal(SIGCHLD, $this->onChildProcessShutdown());
        Process::signal(SIGUSR1, $this->onReopen());
        Process::signal(SIGUSR2, $this->onReload());

        $this->bootstrap();
    }

    public function bootstrap()
    {
        foreach ($this->bootstrapLoaders as $bootstrap) {
            /** @var ProcessHandler $processBootstrap */
            $processBootstrap                    = new $bootstrap($this, $this->container);
            $processId                           = $processBootstrap->run();
            $this->childrenProcesses[$processId] = $processBootstrap;
        }
    }

    /**
     * 设置启动器加载序列
     *
     * @param string[] $bootstrapLoaders
     *
     * @return $this
     */
    protected function bootstrapLoader($bootstrapLoaders)
    {
        $this->bootstrapLoaders = $bootstrapLoaders;

        return $this;
    }

    /**
     * 获取应用核心
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * 进程终结
     */
    protected function terminate()
    {
        flock($this->pidFileHandle, LOCK_UN);
        fclose($this->pidFileHandle);

        @unlink($this->pidFile);
        exit;
    }

    /**
     * @return \Closure
     */
    protected function onTerminating()
    {
        return function ($signal) {
            if (count($this->childrenProcesses)) {
                $this->signal = $signal;

                foreach ($this->childrenProcesses as $process) {
                    $process->kill();
                }
            } else {
                $this->terminate();
            }
        };
    }

    /**
     * 获取收到 SIGKILL 时的处理回调
     *
     * @return \Closure
     */
    protected function onKill()
    {
        return $this->onTerminating();
    }

    /**
     * 获取收到 SIGCHLD 时的处理回调
     *
     * @return \Closure
     */
    protected function onChildProcessShutdown()
    {
        return function () {
            while ($ret = Process::wait(false)) {
                if (SIGUSR1 === $this->signal || SIGKILL === $ret['signal']) {
                    $bootstrap = get_class($this->childrenProcesses[$ret['pid']]);

                    /** @var ProcessHandler $processBootstrap */
                    $processBootstrap                    = new $bootstrap($this);
                    $processId                           = $processBootstrap->run();
                    $this->childrenProcesses[$processId] = $processBootstrap;
                }

                unset($this->childrenProcesses[$ret['pid']]);
            }

            if (SIGTERM === $this->signal || SIGKILL === $this->signal) {
                $this->terminate();
            }
        };
    }

    /**
     * 获取收到 SIGUSR1 时的处理回调
     *
     * @return \Closure
     */
    protected function onReopen()
    {
        return function ($signal) {
            $this->signal = $signal;

            foreach ($this->childrenProcesses as $process) {
                $process->kill();
            }
        };
    }

    /**
     * 获取收到 SIGUSR2 时的处理回调
     *
     * @return \Closure
     */
    protected function onReload()
    {
        return function () {
            foreach ($this->childrenProcesses as $process) {
                $process->kill(SIGUSR1);
            }
        };
    }

}
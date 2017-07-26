<?php
/**
 * ProcessTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Process;

use Dybasedev\Keeper\Process\Process;
use PHPUnit\Framework\TestCase;
use Process\Stubs\ImplementStandardProcess;

class ProcessTest extends TestCase
{

    public function testOptions()
    {
        /** @var Process $process */
        $process = $this->getMockForAbstractClass(Process::class, [['options' => true]]);
        $this->assertTrue($process->getOptions()['options']);
    }

    public function testAutoReloadOption()
    {
        /** @var Process $process */
        $process = $this->getMockForAbstractClass(Process::class);
        $this->assertTrue($process->isAutoReload());

        $process = $this->getMockForAbstractClass(Process::class, [['auto_reload' => true]]);
        $this->assertTrue($process->isAutoReload());

        $process = $this->getMockForAbstractClass(Process::class, [['auto_reload' => false]]);
        $this->assertFalse($process->isAutoReload());
    }

    public function testTemporaryAutoReload()
    {
        /** @var Process $process */
        $process = $this->getMockForAbstractClass(Process::class, [['auto_reload' => false, 'temp_auto_reload' => true]]);
        $this->assertTrue($process->isAutoReload());
        $this->assertTrue($process->isTemporaryAutoReload());

        $process->clearTemporaryAutoLoadStatus();
        $this->assertFalse($process->isAutoReload());
        $this->assertFalse($process->isTemporaryAutoReload());

        $process = $this->getMockForAbstractClass(Process::class, [['auto_reload' => true, 'temp_auto_reload' => true]]);
        $this->assertTrue($process->isAutoReload());
        $this->assertTrue($process->isTemporaryAutoReload());

        $process->clearTemporaryAutoLoadStatus();
        $this->assertTrue($process->isAutoReload());
        $this->assertFalse($process->isTemporaryAutoReload());
    }

    public function testProcessClone()
    {
        $options = ['foo' => true];
        $process = $this->getMockForAbstractClass(Process::class, [$options]);
        $process->runWithProcessController(0);

        $this->assertAttributeEquals($options, 'options', $process);
        $this->assertAttributeEquals(true, 'withProcessController', $process);

        $clone = clone $process;
        $this->assertAttributeEquals($options, 'options', $clone);
        $this->assertAttributeEquals(false, 'withProcessController', $clone);
    }

    public function testBuildSwooleProcessInstance()
    {
        $process = $this->getMockForAbstractClass(ImplementStandardProcess::class);
        $process->expects($this->once())->method('isRedirectStdIO')->willReturn(true);
        $process->expects($this->once())->method('getPipeType')->willReturn(1);
        $this->assertInstanceOf(\Swoole\Process::class, $process->buildSwooleProcessInstance([$process, 'process']));

        $process = $this->getMockForAbstractClass(Process::class);
        $this->assertInstanceOf(\Swoole\Process::class, $process->buildSwooleProcessInstance([$process, 'process']));
    }

}

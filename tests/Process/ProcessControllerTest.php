<?php
/**
 * ProcessControllerTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Process;

use Dybasedev\Keeper\Process\ProcessController;
use PHPUnit\Framework\TestCase;
use Dybasedev\Keeper\Process\Process;
use Process\Stubs\StubExtendProcess;
use Process\Stubs\StubNotExtendProcess;

class ProcessControllerTest extends TestCase
{
    protected $masterProcess;

    public function testMakeAndRegisterProcess()
    {
        $controller = new ProcessController($this->masterProcess);
        $controller->registerProcess($process = new StubExtendProcess());
        $controller->registerProcess(StubExtendProcess::class, ['foo' => 'bar']);

        $this->assertAttributeEquals([$process, new StubExtendProcess(['foo' => 'bar'])], 'registeredProcesses',
            $controller);

        $this->expectException(\InvalidArgumentException::class);
        $controller->registerProcess(new StubNotExtendProcess());
    }

    public function testBuildProcessViaBootstrap()
    {
        $controller = new ProcessController($this->masterProcess);

        $process1 = $this->getMockBuilder(Process::class)
                         ->setMethods(['getProcessId', 'runWithProcessController', 'process'])
                         ->getMock();
        $process1->expects($this->any())->method('getProcessId')->willReturn(100);
        $process1->expects($this->once())
                 ->method('runWithProcessController')
                 ->with($this->equalTo($controller))
                 ->willReturnSelf();

        $process2 = $this->getMockBuilder(Process::class)
                         ->setMethods(['getProcessId', 'runWithProcessController', 'process'])
                         ->getMock();
        $process2->expects($this->any())->method('getProcessId')->willReturn(200);
        $process2->expects($this->once())
                 ->method('runWithProcessController')
                 ->with($this->equalTo($controller))
                 ->willReturnSelf();

        $controller->registerProcesses([$process1, $process2]);
        $controller->bootstrap();
        $this->assertAttributeEquals([100 => $process1, 200 => $process2], 'processes', $controller);
    }


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->masterProcess = $this->getMockBuilder(Process::class)
                                    ->setMethods(['getProcessId', 'process'])
                                    ->getMock();
        $this->masterProcess->expects($this->any())->method('getProcessId')->willReturn(0);
    }


}

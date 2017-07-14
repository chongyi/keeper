<?php
/**
 * ResponseTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Http;

use Dybasedev\Keeper\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Swoole\Http\Response
     */
    protected $swooleResponse;

    public function testResponseContentSend()
    {
        $this->swooleResponse->expects($this->once())->method('end')->with($this->equalTo('response content'));

        (new Response('response content'))->setSwooleResponse($this->swooleResponse)->sendContent();
    }

    public function testResponseHeaderSend()
    {
        $response = new Response('not found', 404);
        $response->headers->set('Cache-Control', 'no-cache, private');
        $response->headers->set('Foo', 'Bar');

        $this->swooleResponse->expects($this->once())->method('status')->with($this->equalTo(404));
        $this->swooleResponse->expects($this->any())
                             ->method('header')
                             ->withConsecutive(
                                 [$this->equalTo('Cache-Control'), $this->equalTo('no-cache, private')],
                                 [$this->equalTo('Date'), $this->equalTo($response->headers->get('Date'))],
                                 [$this->equalTo('Foo'), $this->equalTo('Bar')]
                             );

        $response->setSwooleResponse($this->swooleResponse)->sendHeaders();
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->swooleResponse = $this->getMockBuilder(\Swoole\Http\Response::class)
                                     ->setMethods(['end', 'status', 'header'])
                                     ->getMock();
    }


}

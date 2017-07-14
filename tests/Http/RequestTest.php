<?php
/**
 * RequestTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Http;

use Dybasedev\Keeper\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected $swooleRequest;

    public function testCreateRequestInstance()
    {
        $this->swooleRequest->get = ['foo' => 'test a', 'bar' => 'test b'];
        $request = Request::createFromSwooleRequest($this->swooleRequest);

        $this->assertEquals('test a', $request->get('foo'));
        $this->assertEquals('test b', $request->get('bar'));

        $this->swooleRequest->server = ['remote_addr' => '127.0.0.1', 'request_uri' => '/foo/bar'];
        $this->swooleRequest->header = ['accept' => 'text/html', 'accept-language' => 'zh_CN'];
        $request = Request::createFromSwooleRequest($this->swooleRequest);

        $this->assertEquals('127.0.0.1', $request->getClientIp());
        $this->assertEquals('/foo/bar', $request->getRequestUri());
        $this->assertEquals(['zh_CN'], $request->getLanguages());
        $this->assertEquals(['text/html'], $request->getAcceptableContentTypes());
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->swooleRequest = $this->createMock(\Swoole\Http\Request::class);
    }

}

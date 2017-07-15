<?php
/**
 * LifecycleKernelTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Http;

use Dybasedev\Keeper\Http\Lifecycle\Kernel;
use Dybasedev\Keeper\Http\Response;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LifecycleKernelTest extends TestCase
{
    public function testPrepareResponse()
    {
        /** @var Container $container */
        $container = $this->createMock(Container::class);

        $kernel = new Kernel($container);

        $response = new Response();
        $this->assertEquals($response, $kernel->prepareResponse($response));

        $response = new \Symfony\Component\HttpFoundation\Response('foo', 403);
        $response->setCharset('gbk');
        $this->assertInstanceOf(Response::class, $kernel->prepareResponse($response));
        $this->assertEquals('foo', $kernel->prepareResponse($response)->getContent());
        $this->assertEquals(403, $kernel->prepareResponse($response)->getStatusCode());
        $this->assertEquals($response->headers->all(), $kernel->prepareResponse($response)->headers->all());

        $response = new JsonResponse(['foo' => 'bar']);
        $this->assertInstanceOf(Response::class, $kernel->prepareResponse($response));
        $this->assertEquals('{"foo":"bar"}', $kernel->prepareResponse($response)->getContent());
        $this->assertEquals('application/json', $kernel->prepareResponse($response)->headers->get('Content-Type'));

        $response = 'foo';
        $this->assertInstanceOf(Response::class, $kernel->prepareResponse($response));
        $this->assertEquals('foo', $kernel->prepareResponse($response)->getContent());
        $this->assertEquals(200, $kernel->prepareResponse($response)->getStatusCode());
    }
}

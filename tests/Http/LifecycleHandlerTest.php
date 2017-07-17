<?php
/**
 * LifecycleHandlerTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Http;

use Dybasedev\Keeper\Http\Lifecycle\Handler;
use Dybasedev\Keeper\Http\Lifecycle\RouteDispatcher;
use Dybasedev\Keeper\Http\Request;
use Dybasedev\Keeper\Http\Response;
use Exception;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LifecycleHandlerTest extends TestCase
{
    /**
     * @var Handler
     */
    protected $handler;

    public function testPrepareResponse()
    {
        $response = new Response();
        $this->assertEquals($response, $this->handler->prepareResponse($response));

        $response = new SymfonyResponse('foo', 403);
        $response->setCharset('gbk');
        $this->assertInstanceOf(Response::class, $this->handler->prepareResponse($response));
        $this->assertEquals('foo', $this->handler->prepareResponse($response)->getContent());
        $this->assertEquals(403, $this->handler->prepareResponse($response)->getStatusCode());
        $this->assertEquals($response->headers->all(), $this->handler->prepareResponse($response)->headers->all());

        $response = new JsonResponse(['foo' => 'bar']);
        $this->assertInstanceOf(Response::class, $this->handler->prepareResponse($response));
        $this->assertEquals('{"foo":"bar"}', $this->handler->prepareResponse($response)->getContent());
        $this->assertEquals('application/json',
            $this->handler->prepareResponse($response)->headers->get('Content-Type'));

        $response = 'foo';
        $this->assertInstanceOf(Response::class, $this->handler->prepareResponse($response));
        $this->assertEquals('foo', $this->handler->prepareResponse($response)->getContent());
        $this->assertEquals(200, $this->handler->prepareResponse($response)->getStatusCode());
    }

    public function testExceptionHandler()
    {
        $this->assertInstanceOf(Response::class, $this->handler->handleException(new Exception()));
        $this->assertEquals('foo', $this->handler->handleException(new Exception('foo'))->getContent());
        $this->assertEquals(500, $this->handler->handleException(new Exception())->getStatusCode());
        $this->assertEquals(403, $this->handler->handleException(new HttpException(403))->getStatusCode());

        $this->handler->setExceptionHandler(function (Exception $exception) {
            $this->assertInstanceOf(HttpException::class, $exception);

            return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        });
        $this->assertInstanceOf(SymfonyResponse::class, $this->handler->handleException(new Exception()));
        $this->assertEquals(500, $this->handler->handleException(new Exception())->getStatusCode());
        $this->assertEquals(403, $this->handler->handleException(new HttpException(403))->getStatusCode());
    }

    public function testDispatch()
    {
        $routeDispacther = $this->createMock(RouteDispatcher::class);
        $routeDispacther->expects($this->once())->method('dispatch')->willReturn($stub = new Response('foo'));
        $this->handler->setRouteDispatcher($routeDispacther);
        $this->assertEquals($stub, $this->handler->dispatch(new Request()));

        $routeDispacther = $this->createMock(RouteDispatcher::class);
        $routeDispacther->expects($this->once())->method('dispatch')->willThrowException(new Exception());
        $this->handler->setRouteDispatcher($routeDispacther);
        $this->assertEquals(500, $this->handler->dispatch(new Request())->getStatusCode());
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var Container $container */
        $container = $this->createMock(Container::class);;
        $this->handler = new Handler($container);
    }


}

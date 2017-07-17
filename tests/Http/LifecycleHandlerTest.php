<?php
/**
 * LifecycleHandlerTest.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Http;

use Dybasedev\Keeper\Http\Lifecycle\Handler;
use Dybasedev\Keeper\Http\Response;
use Exception;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LifecycleHandlerTest extends TestCase
{
    public function testPrepareResponse()
    {
        /** @var Container $container */
        $container = $this->createMock(Container::class);

        $kernel = new Handler($container);

        $response = new Response();
        $this->assertEquals($response, $kernel->prepareResponse($response));

        $response = new SymfonyResponse('foo', 403);
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

    public function testExceptionHandler()
    {
        /** @var Container $container */
        $container = $this->createMock(Container::class);

        $kernel = (new Handler($container))->setExceptionHandler(function (Exception $exception) {
            $this->assertInstanceOf(HttpException::class, $exception);

            return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        });
        $this->assertInstanceOf(SymfonyResponse::class, $kernel->handleException(new Exception()));
        $this->assertEquals(500, $kernel->handleException(new Exception())->getStatusCode());
        $this->assertEquals(403, $kernel->handleException(new HttpException(403))->getStatusCode());
    }
}

<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle\Illuminate;

use Dybasedev\Keeper\Http\Lifecycle\Handler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Dybasedev\Keeper\Http\Lifecycle\Interfaces\ExceptionHandler as ExceptionHandlerInterface;
use Dybasedev\Keeper\Http\Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * ExceptionHandler constructor.
     *
     * @param Handler $handler
     */
    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }


    /**
     * 处理异常
     *
     * @param HttpException $exception
     *
     * @return Response
     */
    public function handle(HttpException $exception)
    {
        $debug  = isset($_ENV['debug']) && $_ENV['debug'] ? true : false;
        $handle = (new SymfonyExceptionHandler($debug));

        return new Response($handle->getHtml($exception), $exception->getStatusCode(), $exception->getHeaders());
    }
}
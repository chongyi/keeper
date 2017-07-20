<?php
/**
 * HttpExceptionHandlerTrait.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle;

use Dybasedev\Keeper\Http\Response;
use Dybasedev\Keeper\Http\ServerProcess;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Trait HttpExceptionHandlerTrait
 *
 * @package Dybasedev\Keeper\Http\Lifecycle
 */
trait HttpExceptionHandlerTrait
{
    public function getExceptionHandler()
    {
        return function (HttpException $exception) {
            $process   = $this->getContainer()->make(ServerProcess::class);
            $options   = $process->getOptions();
            $handle = (new ExceptionHandler(isset($options['debug']) && $options['debug'] ? true : false));

            return new Response($handle->getHtml($exception), $exception->getStatusCode(), $exception->getHeaders());

        };
    }

    /**
     * @return Container
     */
    abstract public function getContainer();
}
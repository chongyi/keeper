<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle\Interfaces;

use Dybasedev\Keeper\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Interface ExceptionHandler
 *
 * 异常处理器
 *
 * @package Dybasedev\Keeper\Http\Lifecycle\Interfaces
 */
interface ExceptionHandler
{
    /**
     * 处理异常
     *
     * @param HttpException $exception
     *
     * @return Response
     */
    public function handle(HttpException $exception);
}
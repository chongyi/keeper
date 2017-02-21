<?php
/**
 * LogicException.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/26 18:09
 */

namespace Keeper\Transport\Exceptions;

/**
 * Class LogicException
 *
 * 逻辑异常，往往用在处理具体数据的流程中，遇到一些特殊问题，
 * 引起这些问题的原因不一定是由于参数，可能是因为系统本身机制的缺陷（但不算是致命），例如生成的全局唯一标识符并不唯一。
 *
 * @package Keeper\Transport\Exceptions
 */
class LogicException extends \RuntimeException
{

}
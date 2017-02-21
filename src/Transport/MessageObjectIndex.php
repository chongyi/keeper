<?php
/**
 * MessageObjectIndex.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:57
 */

namespace Keeper\Transport;

use Keeper\Transport\Exceptions\ConflictException;


/**
 * Class MessageObjectMap
 *
 * @package Keeper\Transport
 */
class MessageObjectIndex
{
    /**
     * @var array
     */
    protected static $map = [];

    /**
     * @param $typeIndex
     * @param $handler
     *
     * @throws ConflictException
     */
    public static function register($typeIndex, $handler)
    {
        if (isset(static::$map[$typeIndex])) {
            throw new ConflictException(sprintf("Index %s already exists, handler is %s", $typeIndex,
                static::$map[$typeIndex]));
        }

        static::$map[$typeIndex] = $handler;
    }

    /**
     * @param $typeIndex
     *
     * @return mixed|null
     */
    public static function find($typeIndex)
    {
        if (isset(static::$map[$typeIndex])) {
            return static::$map[$typeIndex];
        }

        return null;
    }

    /**
     * @param $typeHandler
     *
     * @return int
     */
    public static function getIndex($typeHandler)
    {
        return array_search($typeHandler, static::$map);
    }

    /**
     * @return array
     */
    public static function getMap()
    {
        return static::$map;
    }
}
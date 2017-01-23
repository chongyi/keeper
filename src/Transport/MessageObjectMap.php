<?php
/**
 * MessageObjectMap.php
 *
 * Creator:    chongyi
 * Created at: 2016/12/23 11:57
 */

namespace FanaticalPHP\Transport;


/**
 * Class MessageObjectMap
 *
 * @package FanaticalPHP\Transport
 */
class MessageObjectMap
{
    /**
     * @var array
     */
    protected static $map = [];

    /**
     * @param $typeId
     * @param $handler
     */
    public static function register($typeId, $handler)
    {
        static::$map[$typeId] = $handler;
    }

    /**
     * @param $typeId
     *
     * @return mixed|null
     */
    public static function find($typeId)
    {
        if (isset(static::$map[$typeId])) {
            return static::$map[$typeId];
        }

        return null;
    }

    /**
     * @param $typeHandler
     *
     * @return int
     */
    public static function getId($typeHandler)
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
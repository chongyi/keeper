<?php
/**
 * ProcessHandler.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 17:35
 */

namespace FanaticalPHP\Base;


class ProcessHandler
{
    protected $configure;

    /**
     * ProcessHandler constructor.
     *
     * @param $configure
     */
    public function __construct($configure)
    {
        $this->configure = $configure;
    }


    public function run()
    {

    }
}
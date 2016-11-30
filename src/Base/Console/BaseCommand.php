<?php
/**
 * BaseCommand.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 17:37
 */

namespace FanaticalPHP\Base\Console;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    public function loadConfigureFile($configureFile)
    {
        if (!is_file($configureFile)) {
            throw new RuntimeException("Error: Configure file ($configureFile) is not exists!");
        }

        $content = file_get_contents($configureFile);
        $config  = json_decode($content);

        if (is_null($config)) {
            throw new RuntimeException(sprintf("Error: Configure file content cannot be decode. (C:%d Msg:%s)",
                json_last_error(), json_last_error_msg()));
        }

        return $config;
    }
}
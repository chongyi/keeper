<?php
/**
 * EntryController.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 16:51
 */

namespace FanaticalPHP\Base;

use FanaticalPHP\Base\Console\ProcessControl;
use FanaticalPHP\Base\Console\ProcessStart;
use Symfony\Component\Console\Application as SymfonyConsole;

class EntryController
{
    public function execute()
    {
        $console = new SymfonyConsole(Application::APPLICATION_NAME, Application::APPLICATION_VERSION);
        $console->addCommands([
            new ProcessStart(),
            new ProcessControl(),
        ]);

        $console->
        $console->run();
    }
}
<?php
/**
 * ProcessControl.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 17:12
 */

namespace FanaticalPHP\Base\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProcessControl extends BaseCommand
{
    protected function configure()
    {
        $this->setName('process:control')
             ->setDescription('Use to control the project process, you can stop/reopen/reload process.')
             ->addArgument('command', InputArgument::REQUIRED, 'Command list: stop, reopen, reload')
             ->addOption('configure', 'C', InputOption::VALUE_REQUIRED, 'The project process configure file path.',
                 true);
    }
}
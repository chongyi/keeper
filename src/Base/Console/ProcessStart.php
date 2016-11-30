<?php
/**
 * ProcessStart.php
 *
 * Creator:    chongyi
 * Created at: 2016/11/29 16:54
 */

namespace FanaticalPHP\Base\Console;

use FanaticalPHP\Base\ProcessHandler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessStart extends BaseCommand
{
    protected function configure()
    {
        $this->setName('process:start')
             ->setDescription('Start the project.')
             ->addArgument('configure', InputArgument::OPTIONAL, 'The project process configure file path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('configure');

        if (!$configFile) {
            $configFile = FANATICAL_PROJECT_ROOT . '/config/default.json';
        }

        $configure = $this->loadConfigureFile($configFile);

        if (!isset($configure->pid_file)) {
            $configure->pid_file = FANATICAL_PROJECT_ROOT . '/run/fanatical.pid';

            if (!is_dir($path = dirname($configure->pid_file))) {
                @mkdir($path, 0700, true);
            }
        }

        if (is_file($configure->pid_file)) {
            $pid = file_get_contents($configure->pid_file);
            system("ps -$pid", $status);

            if ($status) {
                @unlink($configure->pid_file);
            } else {
                $output->writeln("Have running instance (PID: $pid).");
                exit(1);
            }
        }

        (new ProcessHandler($configure))->run();
    }
}
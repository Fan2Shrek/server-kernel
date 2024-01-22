<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\ServerKernel;

#[AsCommand('start:kernel')]
class StartKernelCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $ouput): int
    {

        $kernel = new ServerKernel($input->getOption('env'), !$input->getOption('no-debug'));
        $kernel->boot();
        $kernel->start();
        $kernel->shutdown();

        return Command::FAILURE;
    }
}

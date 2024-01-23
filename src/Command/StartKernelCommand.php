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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting kernel...');
        $kernel = new ServerKernel($input->getOption('env'), !$input->getOption('no-debug'));

        $kernel->boot();
        $output->writeln('Starting booted...');

        $output->writeln('Kernel is ready to handle connection !');
        try {
            $kernel->start();
        } catch (\Throwable $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }

        $kernel->shutdown();
        $output->writeln('Shutting down...');

        return Command::FAILURE;
    }
}

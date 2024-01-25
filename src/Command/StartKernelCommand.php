<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\ServerKernel;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('start:kernel')]
class StartKernelCommand extends Command
{
    /**
     * @todo Add port || host options
     */
    public function configure(): void
    {
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $_ENV['KERNEL_PORT']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting kernel...');
        $kernel = new ServerKernel($input->getOption('env'), !$input->getOption('no-debug'), $input->getOption('port'));

        $kernel->boot();
        $output->writeln('Starting booted...');

        $output->writeln('Kernel is ready to handle connection !');
        try {
            $kernel->start();
        } catch (\Throwable $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }

        $kernel->stop();
        $output->writeln('Shutting down...');

        return Command::FAILURE;
    }
}

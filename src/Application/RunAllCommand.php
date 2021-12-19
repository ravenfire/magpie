<?php

namespace Ravenfire\Magpie\Application;

use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAllCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'run:all';
    protected static $defaultDescription = "Runs all Sources";

    protected function configure(): void
    {
        $this->setHelp("Run All Sources");
        // Adding arguments here
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output));
        $this->getContext()->getLogger()->warning("Testing");

        foreach ($this->getContext()->getAllSources() as $source) {
            $source->run($output);
        }

        $this->getContext()->getLogger()->info("Done");

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
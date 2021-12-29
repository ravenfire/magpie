<?php

namespace Ravenfire\Magpie\Application;

use Ravenfire\Magpie\Data\Jobs\Job;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAllCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'run:all';
    protected static $defaultDescription = "Runs a new job with all sources";

    protected function configure(): void
    {
        $this->setHelp("Run All Sources"); // @todo
        // Adding arguments here
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output)); //@todo intialize logger like other commands 

        $job = new Job();
        $job->name = Job::createName(); // @todo: all this to be passed in
        $job->save();

        foreach ($this->getContext()->getAllSources() as $source) {
            $source->run($job, $output);
        }

        $this->getContext()->getLogger()->info("Done");

        return Command::SUCCESS;
    }
}
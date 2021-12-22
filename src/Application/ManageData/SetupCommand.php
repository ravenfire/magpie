<?php

namespace Ravenfire\Magpie\Application\ManageData;

use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Ravenfire\Magpie\Data\Migrations\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SetupCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'data:setup';
    protected static $defaultDescription = "Run all migrations";

    protected function configure(): void
    {
        $this->setHelp("Creates all tables for all sources");
        // Adding arguments here
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrations = new MigrationManager($this->getContext());

        // First we run the Magpie Migrations
        $this->getContext()->getLogger()->debug("Running Magpie Migrations");
        $migrations->upMagpie(function ($migration_class) {
            $this->getContext()->getLogger()->debug("Running Migration: `{$migration_class}`");
        });

        $this->getContext()->getLogger()->debug("Finished With All Magpie Migrations");

        // Second, we create the Primary Entity
        $primary_entity = $this->getContext()->getPrimaryEntity();
        $migrations->up(
            $primary_entity::getMigrations(),
            function ($migration_class) use ($primary_entity) {
                $this->getContext()->getLogger()->debug("Running Primary Entity Migrations: `{$primary_entity::getKey()}`");
            }
        );

        // Now, we can install sources if we want
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Would you like to install ALL sources now? (you can install them individually with `magpie install <source>`', true);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $install_all_command = $this->getApplication()->find('install');

        $arguments = [
            'key' => ':all',
        ];

        $input = new ArrayInput($arguments);
        return $install_all_command->run($input, $output);
    }
}
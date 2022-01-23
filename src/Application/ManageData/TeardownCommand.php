<?php

namespace Ravenfire\Magpie\Application\ManageData;

use Exception;
use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Ravenfire\Magpie\Data\Migrations\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Creates teardown command.
 */
class TeardownCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'data:teardown';
    protected static $defaultDescription = "Reverse all migrations";

    /**
     * Takes user inputs.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp("Destroys all tables for all sources");
        // Adding arguments here
    }

    /**
     *
     *
     * @throws ExceptionInterface
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will delete all Magpie data and all sources. Continue?', false);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Probably a good choice");
            return Command::SUCCESS;
        }

        // First, we uninstall all the sources
        $uninstall_all_command = $this->getApplication()->find('uninstall');
        $arguments = [
            'key'       => ':all',
            '--confirm' => true
        ];

        $input = new ArrayInput($arguments);
        $code = $uninstall_all_command->run($input, $output);

        if ($code !== Command::SUCCESS) {
            throw new Exception("Uninstalls failed");
        }

        // Next, we remove the primary entity
        $migrations = new MigrationManager($this->getContext());
        $primary_entity = $this->getContext()->getPrimaryEntity();
        $migrations->down(
            $primary_entity::getMigrations(),
            function ($migration_class) use ($primary_entity) {
                $this->getContext()->getLogger()->debug("Reversing Primary Entity Migrations: `{$primary_entity::getKey()}`");
            }
        );

        // Now, we remove The Magpie data
        $this->getContext()->getLogger()->alert("Reversing Magpie Migrations");
        $migrations->downMagpie(function ($migration_class) {
            $this->getContext()->getLogger()->alert("Reversing Migration: `{$migration_class}`");
        });

        return Command::SUCCESS;
    }
}

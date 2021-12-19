<?php

namespace Ravenfire\Magpie\Application\ManageData;

use Exception;
use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UninstallCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'uninstall';
    protected static $defaultDescription = "Uninstalls all Sources";

    protected function configure(): void
    {
        $this->setHelp("Uninstalls All Sources");
        $this
            // configure an argument
            ->addArgument('key', InputArgument::REQUIRED, 'Which source to install (:all for everything)')
            ->addOption('confirm', '-c', InputOption::VALUE_OPTIONAL, 'Confirm?', false);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getContext()->getLogger()->info("Uninstalling All Sources");

        $key = $input->getArgument('key');

        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("Are you sure you want to uninstall {$key}? ", false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("Probably a good choice");
                return Command::SUCCESS;
            }
        }

        $sources = [];
        if ($key === ":all") {
            $sources = $this->getContext()->getAllSources();
        } else {
            $source = $this->getContext()->getSource($key);
            if ($source) {
                $sources = [$source];
            } else {
                throw new Exception("Could not find {$key} to uninstall");
            }
        }

        foreach ($sources as $source) {
            $this->getContext()->getLogger()->info("Uninstalling {$source::getKey()}");
            $source->uninstall();
            $this->getContext()->getLogger()->info("{$source::getKey()} Uninstall Complete");
        }

        $this->getContext()->getLogger()->info("Uninstalls Complete");

        return Command::SUCCESS;
    }
}
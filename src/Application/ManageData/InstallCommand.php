<?php

namespace Ravenfire\Magpie\Application\ManageData;

use Exception;
use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends AbstractMagpieCommand
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = "Installs all Sources";

    protected function configure(): void
    {
        $this->setHelp("Installs Sources");
        $this
            // configure an argument
            ->addArgument('key', InputArgument::REQUIRED, 'Which source to install (:all for everything)');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getContext()->getLogger()->info("Installing Sources");

        $sources = [];

        $key = $input->getArgument('key');
        if ($key === ":all") {
            $sources = array_values($this->getContext()->getAllSources());
        } else {
            $source = $this->getContext()->getSource($key);
            if ($source) {
                $sources = [$source];
            } else {
                throw new Exception("Could not find {$key} to install");
            }
        }

        foreach ($sources as $source) {
            $this->getContext()->getLogger()->info("Installing {$source::getKey()}");
            $source->install();
            $this->getContext()->getLogger()->info("{$source::getKey()} Install Complete");
        }

        $this->getContext()->getLogger()->info("Installs Complete");

        return Command::SUCCESS;
    }
}
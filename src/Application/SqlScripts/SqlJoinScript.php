<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Support\Facades\DB;
use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SqlJoinScript extends AbstractMagpieCommand
{
    protected static $defaultName = 'sql:join';
    protected static $defaultDescription = "Sql query counting the number of every group in a column";

    protected function configure(): void
    {
        $this->setHelp("Sql query counting the number of every group in a column");
        $this->addArgument('tableOne', InputArgument::REQUIRED, "First table to use");
        $this->addArgument('tableTwo', InputArgument::REQUIRED, "Second table to use");
        $this->addArgument('tableOneColumn', InputArgument::REQUIRED, "Table one column to join");
        $this->addArgument('tableTwoColumn', InputArgument::REQUIRED, "Table two column to join");
        $this->addOption('confirm', '-c', InputOption::VALUE_OPTIONAL, 'Confirm?', false);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output));

        $tableOne = $input->getArgument('tableOne');
        $tableTwo = $input->getArgument('tableTwo');
        $tableOneJoinColumn = $input->getArgument('tableOneColumn');
        $tableTwoJoinColumn = $input->getArgument('tableTwoColumn');

        $sql = "";
        $sql .= "SELECT * ";
        $sql .= "FROM {$tableOne} ";
        $sql .= "JOIN {$tableTwo} ON {$tableOneJoinColumn} = {$tableTwoJoinColumn}";

        $results = DB::select($sql)->get();

        dd($results);

        $table_helper = new Table();
        $table_helper->setRows($results);
        $table_helper->setHeaders(['Name', 'Counts']);
        $table_helper->render();

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }
}
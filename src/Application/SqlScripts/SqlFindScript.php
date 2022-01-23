<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Database\Capsule\Manager as DB;
use Ravenfire\Magpie\Application\AbstractMagpieCommand;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SqlFindScript extends AbstractMagpieCommand
{
    protected static $defaultName = 'sql:find';
    protected static $defaultDescription = "Sql query counting the number of every group in a column";

    protected function configure(): void
    {
        $this->setHelp("Sql query counting the number of every group in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Table to use");
        $this->addArgument('column', InputArgument::REQUIRED, "Column to use");
        $this->addArgument('value', InputArgument::REQUIRED, "Value to use");
        $this->addOption('columnName', '-cn', InputOption::VALUE_OPTIONAL, "Column Name to use", 'Data');
        $this->addOption('valueName', '-vn', InputOption::VALUE_OPTIONAL, "Column Name to use", 'Value');
        $this->addOption('confirm', '-c', InputOption::VALUE_OPTIONAL, 'Confirm?', false);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output));

        $table = $input->getArgument('table');
        $column = $input->getArgument('column');
        $value = $input->getArgument('value');
        $columnName = $input->getOption('columnName');
        $valueName = $input->getOption('valueName');

        $results = $this->index($table, $column, $value);

        foreach ($results[0] as $result => $data) {
            $dbColumns[] = $result;
        }

        $rows = [];
        $row = [];
        foreach ($results as $result) {
            foreach ($dbColumns as $dbColumn) {
//                dd($result->$dbColumn);
                $row[] = $result->$dbColumn;
            }
            $rows[] = $row;
            $row = [];
        }

        $count = count($dbColumns);

        $table_helper = new Table($output);
        $table_helper->setRows($rows);
        $table_helper->setHeaders($dbColumns);
        for ($i = 0; $i < $count; $i++) {
            $table_helper->setColumnMaxWidth($i, 12);
        }
        $table_helper->render();

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }

    public function index($table, $column, $value)
    {
        $sql = "";
        $sql .= "SELECT * ";
        $sql .= "FROM {$table} ";
        $sql .= "WHERE {$column} = {$value}";

        return DB::select($sql);
    }
}
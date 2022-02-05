<?php

namespace Ravenfire\Magpie\Ravenfire\Sql;

use Illuminate\Database\Capsule\Manager as DB;
use Ravenfire\Magpie\Application\MagpieCommand;
use Ravenfire\Magpie\Application\SqlScripts\CanHandleSql;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Table with results matching a designated value.
 */
class SqlFindScript extends MagpieCommand
{
    use CanHandleSql;

    protected static $defaultName = 'sql:find';
    protected static $defaultDescription = "Sql query finding a specific value in a column";

    /**
     * Takes uses inputs
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp("Sql query finding specific data in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Add table");
        $this->addArgument('column', InputArgument::REQUIRED, 'Add column');
        $this->addArgument('data', InputArgument::REQUIRED, 'Add data');
        $this->addOption('operator', '-o', InputOption::VALUE_OPTIONAL, 'Add operator', '=');
        $this->addOption('column_length', '-l', InputOption::VALUE_OPTIONAL, 'Define column length', 12);
    }

    /**
     * Builds a table from the sql script based off of user inputs
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output));

        $table = $input->getArgument('table');
        $column = $input->getArgument('column');
        $data = $input->getArgument('data');
        $operator = $input->getOption('operator');
        $length = $input->getOption('column_length');

        $results = $this->index($table, $column, $data, $operator);

        $db_columns = [];

        $rows = $this->useAllColumnsHandler($results, $db_columns, $length);

        $this->createTable($output, $rows, $db_columns);

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }

    /**
     * Creates the sql script which finds a designated value.
     *
     * @param $table
     * @param $column
     * @param $value
     * @return mixed
     */
    public function index($table, $column, $value, $operator)
    {
        if ($this->checkTableAndColumnExist($table, $column)) {
            $sql = "";
            $sql .= "SELECT * ";
            $sql .= "FROM {$table} ";
            $sql .= "WHERE {$column} {$operator} ?";

            return DB::select($sql, [$value]);
        }
        return print_r("Bad data.");
    }
}
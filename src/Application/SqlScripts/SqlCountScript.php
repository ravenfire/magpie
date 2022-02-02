<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Database\Capsule\Manager as DB;
use Ravenfire\Magpie\Application\MagpieCommand;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  Table counting values from a designated column.
 */
class SqlCountScript extends MagpieCommand
{
    use CanHandleSql;

    protected static $defaultName = 'sql:count';
    protected static $defaultDescription = "Sql query counting the number of every group in a column";

    /**
     * Takes uses inputs
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp("Sql query counting the number of every group in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Table to use");
        $this->addArgument('column', InputArgument::REQUIRED, "Column to use");
        $this->addArgument('columnName', InputArgument::REQUIRED, "Name to use for the column");
        $this->addOption('DESC or ASC', '-asc', InputOption::VALUE_OPTIONAL, 'DESC or ASC?', 'DESC');
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
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output)); //@todo intialize logger like other commands

        $table = $input->getArgument('table');
        $column = $input->getArgument('column');
        $column_name = $input->getArgument('columnName');

        $results = $this->index($table, $column, $column_name);

        $rows = [];
        foreach ($results as $result) {
            $rows[] = array($result->$column_name, $result->Count);
        }

        $table_helper = new Table($output);
        $table_helper->setRows($rows);
        $table_helper->setHeaders([$column_name, 'Count']);
        $table_helper->render();

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }

    /**
     * Creates a sql script which counts each value in a designated column.
     *
     * @param $table
     * @param $column
     * @param $column_name
     * @return mixed
     */
    public function index($table, $column, $column_name)
    {
        if ($this->checkTableAndColumnExist($table, $column)) {
            $sql = "";
            $sql .= "SELECT COUNT({$column}) AS 'Count', $column AS '$column_name'";
            $sql .= "FROM {$table} ";
            $sql .= "GROUP BY {$column} ";
            $sql .= "ORDER BY COUNT({$column}) DESC";

            return DB::select($sql);
        }
        return print_r("Bad data");
    }
}
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
 *  Table counting values from a designated column.
 */
class SqlCountScript extends MagpieCommand
{
    use CanHandleSql;

    protected static $defaultName = 'sql:count';
    protected static $defaultDescription = "Sql query counting the number of every group in a column";

    /**
     * Takes users inputs
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp("Sql query counting the number of every group in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Add table");
        $this->addArgument('column', InputArgument::REQUIRED, 'Add column');
        $this->addOption('columnName', '-c', InputOption::VALUE_OPTIONAL, 'Add columnName');
        $this->addOption('priority', '-p', InputOption::VALUE_OPTIONAL, 'Add ASC/DESC', 'DESC');
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
        $column_name = $input->getOption('columnName');
        if ($column_name === null) $column_name = $column;
        $priority = $input->getOption('priority');

        $results = $this->index($table, $column, $column_name, $priority);

        $rows = [];
        foreach ($results as $result) {
            $rows[] = array($result->$column_name, $result->Count);
        }

        $this->createTable($output, $rows, [$column_name, 'Count']);

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
    public function index($table, $column, $column_name = null, $priority = null)
    {
        if ($this->checkTableAndColumnExist($table, $column)) {
            $sql = "";
            $sql .= "SELECT ";
            $column_name !== null ? $sql .= "{$column} AS '{$column_name}', " : $sql .= "{$column}, ";
            $sql .= "COUNT({$column}) AS 'Count'";
            $sql .= "FROM {$table} ";
            $sql .= "GROUP BY {$column} ";
            $sql .= "ORDER BY COUNT({$column})";
            $priority !== null ? $sql .= " $priority;" : $sql .= ";";

            return DB::select($sql);
        }
        return print_r("Bad data");
    }
}
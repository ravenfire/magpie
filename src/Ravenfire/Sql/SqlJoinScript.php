<?php

namespace Ravenfire\Magpie\Ravenfire\Sql;

use Illuminate\Database\Capsule\Manager as DB;
use Ravenfire\Magpie\Application\MagpieCommand;
use Ravenfire\Magpie\Application\SqlScripts\CanHandleSql;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Joins two tables.
 */
class SqlJoinScript extends MagpieCommand
{
    use CanHandleSql;

    protected static $defaultName = 'sql:join';
    protected static $defaultDescription = "Sql query joining two tables";

    /**
     * Takes uses inputs
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp("Sql query joining two tables");
        $this->addArgument('table_one', InputArgument::REQUIRED, 'Add table_one');
        $this->addArgument('table_two', InputArgument::REQUIRED, 'Add table_two');
        $this->addArgument('table_one_join_column', InputArgument::REQUIRED, 'Add column one');
        $this->addArgument('table_two_join_column', InputArgument::REQUIRED, 'Add column two');
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

        $table_one = $input->getArgument('table_one');
        $table_two = $input->getArgument('table_two');
        $table_one_join_column = $input->getArgument('table_one_join_column');
        $table_two_join_column = $input->getArgument('table_two_join_column');

        $results = $this->index($table_one, $table_two, $table_one_join_column, $table_two_join_column);

        $db_columns = [];

        $rows = $this->useAllColumnsHandler($results, $db_columns, 12);

        $this->createTable($output, $rows, $db_columns);

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }

    /**
     * Creates sql script which joins two tables.
     *
     * @param $table_one
     * @param $table_two
     * @param $table_one_join_column
     * @param $table_two_join_column
     * @return mixed
     */
    public function index($table_one, $table_two, $table_one_join_column, $table_two_join_column)
    {
        if ($this->checkTableAndColumnExist($table_one, $table_one_join_column) and $this->checkTableAndColumnExist($table_two, $table_two_join_column)) {
            $sql = "";
            $sql .= "SELECT * ";
            $sql .= "FROM {$table_one} ";
            $sql .= "JOIN {$table_two} ON {$table_one}.{$table_one_join_column} = {$table_two}.{$table_two_join_column} ";

            return DB::select($sql);
        }
        return print_r("Bad data");
    }
}
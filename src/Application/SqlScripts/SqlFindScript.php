<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Database\Capsule\Manager as DB;
use Ravenfire\Magpie\Application\MagpieCommand;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        $this->setHelp("Sql query finding a specific value in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Table to use");
        $this->addArgument('column', InputArgument::REQUIRED, "Column to use");
        $this->addArgument('value', InputArgument::REQUIRED, "Value to use");
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
        $value = $input->getArgument('value');

        $results = $this->index($table, $column, $value);

        $db_columns = [];

        $rows = $this->handleResults($results, $db_columns);

        $table_helper = new Table($output);
        $table_helper->setRows($rows);
        $table_helper->setHeaders($db_columns);
        $table_helper->render();

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
    public function index($table, $column, $value)
    {
        $sql = "";
        $sql .= "SELECT * ";
        $sql .= "FROM {$table} ";
        $sql .= "WHERE {$column} = {$value}";

        return DB::select($sql);
    }
}
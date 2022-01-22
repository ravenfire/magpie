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


class SqlCountScript extends AbstractMagpieCommand
{
    protected static $defaultName = 'sql:count';
    protected static $defaultDescription = "Sql query counting the number of every group in a column";

    protected function configure(): void
    {
        $this->setHelp("Sql query counting the number of every group in a column");
        $this->addArgument('table', InputArgument::REQUIRED, "Table to use");
        $this->addArgument('column', InputArgument::REQUIRED, "Column to use");
        $this->addOption('DESC or ASC', '-asc', InputOption::VALUE_OPTIONAL, 'DESC or ASC?', 'DESC');
        $this->addOption('confirm', '-c', InputOption::VALUE_OPTIONAL, 'Confirm?', false);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output)); //@todo intialize logger like other commands

        $table = $input->getArgument('table');
        $column = $input->getArgument('column');

        $results = $this->index($table, $column);

        dd($results);

//        $sql = "";
//        $sql .= "SELECT COUNT({$column}), $column ";
//        $sql .= "FROM {$table} ";
//        $sql .= "GROUP BY {$column} ";
//        $sql .= "ORDER BY COUNT({$column}) DESC";

//        $results = DB::select($sql);

        $table_helper = new Table($output);
        $table_helper->setRows($results);
        $table_helper->setHeaders(['Name', 'Counts']);
        $table_helper->render();

        $this->getContext()->getLogger()->info("Done");

        return COMMAND::SUCCESS;
    }

    public function index($table, $column)
    {
        $sql = "";
        $sql .= "SELECT COUNT({$column}), $column ";
        $sql .= "FROM {$table} ";
        $sql .= "GROUP BY {$column} ";
        $sql .= "ORDER BY COUNT({$column}) DESC";

        $results = DB::select($sql);
//        $users = DB::select('select * from users where active = ?', [1]);

        return $results;
    }
}
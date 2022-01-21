<?php

namespace Ravenfire\Magpie\Application;

use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';

class SqlCommands extends AbstractMagpieCommand
{
    protected static $defaultName = 'sql:find';
    protected static $defaultDescription = "Runs a new sql query";

    public function myFirstSql($select, $table, $column, $value)
    {
        $sql = "select {$select} from {$table} where {$column} = '{$value}'";
        $results = DB::select($sql);
    }

    public function mySecondSql($select, $table, $column)
    {
        $sql = "select {$select} from {$table}";
        $results = DB::select($sql)
            ->groupBy("{$column}")
            ->count()
            ->get();
    }

    public function myThirdSql($select, $table, $addTable, $tableColumnJoin, $addTableColumnJoin)
    {
        $sql = "select {$select} from {$table}";
        $results = DB::select($sql)
            ->join($addTable, $tableColumnJoin, "=", $addTableColumnJoin)
            ->get();
    }
}
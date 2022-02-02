<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Traits used by the sql scripts
 */
trait CanHandleSql
{
    /**
     * Limits the width of table columns to 12 characters.
     *
     * @param $results
     * @param $db_columns
     * @return array
     */
    public function handleResults($results, &$db_columns)
    {
        foreach ($results[0] as $result => $data) {
            $db_columns[] = $result;
        }

        foreach ($results as $result) {
            $row = [];
            foreach ($db_columns as $db_column) {
                if ($result->$db_column !== null) {
                    if (strlen($result->$db_column) > 12) {
                        $result->$db_column = substr($result->$db_column, 0, 12);
                    }
                }
                $row[] = $result->$db_column;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Verifies table and column info exists
     *
     * @param $table
     * @param $column
     * @return void
     */
    public function checkTableAndColumnExist($table, $column): bool
    {
        if (DB::schema()->hasTable($table) and DB::schema()->hasColumn($table, $column)) {
            return true;
        }
        return false;
    }
}
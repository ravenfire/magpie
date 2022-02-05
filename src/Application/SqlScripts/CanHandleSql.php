<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Helper\Table;

/**
 * Traits used by the sql scripts
 */
trait CanHandleSql
{
    /**
     * Creates table for sql scripts.
     *
     * @param $output
     * @param $rows
     * @param $headers
     * @return void
     */
    public function createTable($output, $rows, $headers)
    {
        $table_helper = new Table($output);
        $table_helper->setRows($rows);
        $table_helper->setHeaders($headers);
        $table_helper->render();
    }

    /**
     * Limits the width of table columns to given length.
     *
     * @param $results
     * @param $db_columns
     * @return array
     */
    public function useAllColumnsHandler($results, &$db_columns, int $columnLength)
    {
        foreach ($results[0] as $result => $data) {
            $db_columns[] = $result;
        }

        foreach ($results as $result) {
            $row = [];
            foreach ($db_columns as $db_column) {
                if ($result->$db_column !== null) {
                    if (strlen($result->$db_column) > $columnLength) {
                        $result->$db_column = substr($result->$db_column, 0, $columnLength);
                    }
                }
                $row[] = $result->$db_column;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Verifies tables and columns exist
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
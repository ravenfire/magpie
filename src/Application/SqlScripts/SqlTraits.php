<?php

namespace Ravenfire\Magpie\Application\SqlScripts;

/**
 * Traits used by the sql scripts
 */
trait SqlTraits
{
    /**
     * Limits the width of table columns to 12 characters.
     *
     * @param $results
     * @param $db_columns
     * @return array
     */
    public function setStrLen($results, $db_columns)
    {
        foreach ($results[0] as $result => $data) {
            $db_columns[] = $result;
        }

        foreach ($results as $result) {
            $row = [];
            foreach ($db_columns as $db_column) {
                if (strlen($result->$db_column) > 12) {
                    $result->$db_column = substr($result->$db_column, 0, 12);
                }
                $row[] = $result->$db_column;
            }
            $rows[] = $row;
        }
        return $rows;
    }
}
<?php


trait trait_append_query_data
{
    /**
     * Used to append a snippet of a MySQL query to an already set MySQL query variable. If $var isn't whitespace,
     * pushes $var to the array set in $pdo and returns the $query_append string.
     *
     * @param $var mixed The variable being checked.
     * @param $query_append string The MySQL query snippet that needs to be added if $var isn't whitespace.
     * @param array $pdo The PDO array that will be used for the PDO query.
     * @return string If $var is not whitespace, returns $query_append. Else, returns whitespace.
     */
    protected function append_query_data($var, $query_append, array &$pdo)
    {
        if (trim($var) !== '')
        {
            $pdo[] = $var;
            return $query_append;
        } else {
            return '';
        }
    }
}
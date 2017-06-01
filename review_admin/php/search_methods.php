<?php

trait search_methods
{
    protected $pdo_array = array();

    protected function unset_empties(&$array)
    {
        foreach ($array as $key=>$array_element)
        {
            if (trim($array_element) == '')
            {
                unset($array[$key]);
            }
        }
    }

    protected function order_date_array($array)
    {
        $tmp = array();
        $out = array();
        $count = count($array);

        if ($count <= 1)
        {
            return array_values($array);
        }

        foreach ($array as $value)
        {
            $tmp[] = strtotime($value);
        }

        sort($tmp);
        foreach ($tmp as $value)
        {
            $out[] = date('Y-m-d H:i:s', $value);
        }

        return array_values($out);
    }

    protected function build_query($table, $search_vars, $date_array, $row_start = null, $row_end = null, $all_columns = false)
    {
        $date_format = 'Y-m-d H:i:s';
        $pdo_array = array();

        $date_array_count = count($date_array);

        if ($all_columns === false)
        {
            $query = "SELECT COUNT(*) FROM $table";
        } else {
            $query = "SELECT * FROM $table";
        }

        if (!empty($search_vars) || !empty($date_array))
        {
            $query .= " WHERE";
        }

        foreach ($search_vars as $key=> $value)
        {
            $query .= " $key LIKE ? AND";
            $pdo_array[] = $value;
        }

        switch ($date_array_count)
        {
            case '0':
                break;
            case '1':
                $query .= " create_date > ? AND create_date < ? AND";
                $pdo_array[] = date($date_format, strtotime($date_array[0]));
                $pdo_array[] = date($date_format, strtotime($date_array[0]) + 86400);
                break;
            case '2':
            case '3':
                $query .= " create_date > ? AND create_date < ? AND";
                $pdo_array[] = date($date_format, strtotime($date_array[0]));
                $pdo_array[] = date($date_format, strtotime($date_array[1]));
                break;
            default:
                break;
        }

        $query = rtrim($query, ' AND');

        if ($all_columns !== false)
        {
            $query .= " ORDER by create_date desc";
            $query .= " LIMIT ?,?";
            $pdo_array[] = $row_start;
            $pdo_array[] = $row_end;
        }

        $this->pdo_array = $pdo_array;

        return $query;
    }
}
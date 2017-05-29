<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require_once('search_methods.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

class products_search {

    protected $search_vars;
    protected $create_date_array;

    protected $row_start;
    protected $row_end;
    protected $all_columns;

    protected $query;

    protected $results;

    use search_methods;

    public function __construct($product_name, $product_id, $date_set, $date_start, $date_end, $row_start = null, $row_end = null, $all_columns = false)
    {
        $this->search_vars['product_name'] = $product_name;
        $this->search_vars['product_id'] = $product_id;
        $this->create_date_array[] = $date_set;
        $this->create_date_array[] = $date_start;
        $this->create_date_array[] = $date_end;

        $this->row_start = $row_start;
        $this->row_end   = $row_end;
        $this->all_columns = $all_columns;

        $this->unset_empties($this->search_vars);
        $this->unset_empties($this->create_date_array);

        $this->create_date_array = $this->order_date_array($this->create_date_array);

        $this->query = $this->build_query('products', $this->search_vars, $this->create_date_array, $this->row_start, $this->row_end, $this->all_columns);

        $this->results = $this->set_result($this->query, $this->pdo_array, $this->all_columns);

    }
    protected function set_result($query, array $pdo_array, $all_columns)
    {
        switch ($all_columns)
        {
            case true:
                $results = sql_pdo::run($query, $pdo_array)->fetchAll();
                return $results;
            case false:
                $results = sql_pdo::run($query, $pdo_array)->fetchColumn();
                return $results;
        }
    }

    public function return_results()
    {
        return $this->results;
    }
}

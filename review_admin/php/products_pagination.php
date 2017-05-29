<?php

require_once('products_search.php');
require_once(RIGBY_ROOT . '/php/paginator_abstract.php');


class products_pagination extends paginator_abstract
{
    protected $product_name;
    protected $product_id;
    protected $date_set;
    protected $date_start;
    protected $date_end;
    protected $query_is_count = true;

    protected $query;

    public function __construct($page, $results_per_page, $buttons_per_bar, $ajax_url = '')
    {
        $this->product_name = isset($_GET['product_name'])  ? htmlspecialchars($_GET['product_name']) : '';
        $this->product_id   = isset($_GET['product_id'])    ? htmlspecialchars($_GET['product_id']) : '';
        $this->date_set     = isset($_GET['date_set'])      ? htmlspecialchars($_GET['date_set']) : '';
        $this->date_start   = isset($_GET['date_start'])    ? htmlspecialchars($_GET['date_start']) : '';
        $this->date_end     = isset($_GET['date_end'])      ? htmlspecialchars($_GET['date_end']) : '';

        parent::__construct($page, $results_per_page, $buttons_per_bar, $ajax_url);
    }

    protected  function set_result_count()
    {
        $get_product_count = new products_search($this->product_name, $this->product_id, $this->date_set, $this->date_start, $this->date_end);
        return $get_product_count->return_results();
    }

    protected  function set_url_query_string()
    {
        $query_string = '';
        $tmp = array();
        $tmp[] = $this->product_name;
        $tmp[] = $this->product_id;
        $tmp[] = $this->date_set;
        $tmp[] = $this->date_start;
        $tmp[] = $this->date_end;

        foreach ($tmp as $key=>$value)
        {
            if (trim($value) !== '')
            {
                $value = htmlspecialchars($value);
                $query_string .= "&$key=$value";
            }
        }
        return $query_string;
    }
}
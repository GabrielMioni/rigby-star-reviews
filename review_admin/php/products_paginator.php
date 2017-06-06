<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/abstract/abstract_paginator.php');
require_once(RIGBY_ROOT . '/widgets/abstract/trait_append_query_data.php');

class products_paginator extends abstract_paginator {

    use trait_append_query_data;

    protected $product_name;
    protected $product_id;

    public function __construct(array $settings_array = array())
    {
        $this->product_id   = $this->check_get('', 'product_id');
        $this->product_name = $this->check_get('', 'product_name');

        parent::__construct($settings_array);
    }

    protected function set_result_count()
    {
        $product_id   = $this->product_id;
        $product_name = $this->product_name;

        $pdo = array();

        $query = "SELECT count(*) FROM products";

        if ($product_id && $product_name)
        {
            $query .= " WHERE";
        }
        $query .= $this->append_query_data($product_id, ' product_id = ?', $pdo);
        $query .= ($product_id && $product_name) ? ' AND' : '';
        $query .= $this->append_query_data($product_name, ' product_name = ?', $pdo);

        try {
            $result = sql_pdo::run($query, $pdo)->fetchColumn();
            return $result;
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    protected function set_url_query_string()
    {
        $product_id   = $this->product_id;
        $product_name = $this->product_name;

        $query_string = '';

        switch ($product_id)
        {
            case '':
                break;
            case false:
                break;
            default:
                $query_string .= '&product_id=' . $product_id;
                break;
        }
        switch ($product_name)
        {
            case '':
                break;
            case false:
                break;
            default:
                $query_string .= '&product_name=' . $product_name;
                break;
        }
        return $query_string;
    }
}

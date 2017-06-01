<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');


class product_array_set
{
    static function get_product_array()
    {
        $tmp = array();
        $query = "SELECT product_id, product_name FROM products";

        try {
            $results =  sql_pdo::run($query)->fetchAll();
            foreach ($results as $product)
            {
                $product_id = $product['product_id'];
                $product_name = $product['product_name'];
                $tmp[$product_id] = $product_name;
            }
        } catch (PDOException $e)
        {
            error_log($e->getMessage());
        }
        return $tmp;
    }
}
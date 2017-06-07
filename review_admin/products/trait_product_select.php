<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
require_once(RIGBY_ROOT . '/widgets/abstract/trait_data_collect.php');

trait trait_product_select
{
    use trait_data_collect;

    protected function product_select($select_type = 1, $sql_start = null, $results_per_page = null)
    {
        $product_name = isset($_GET['product_name']) ? htmlspecialchars($_GET['product_name']) : false;
        $product_id   = isset($_GET['product_id'])   ? htmlspecialchars($_GET['product_id'])   : false;

        switch ($select_type)
        {
            case 0:
                $query = 'SELECT count(*) FROM products p';
                break;
            case 1:
            default:
                $query = 'SELECT p.id, p.product_name, p.product_id, p.create_date, p.last_review, COUNT(r.product) as review_count 
                          FROM products p
                          LEFT JOIN star_reviews r
                          ON r.product = p.product_id';
                break;
        }

        $pdo = array();

        $query .= ($product_id || $product_name) ? ' WHERE' : '';

        if ($product_name !== false)
        {
            $query .= ' p.product_name = ?';
            $pdo[] = $product_name;
        }

        $query .= ($product_id && $product_name) ? ' AND' : '';

        if ($product_id !== false)
        {
            $query .= ' p.product_id = ?';
            $pdo[] = $product_id;
        }

        if ($select_type !== 0)
        {
            $query .= ' GROUP BY p.product_id ORDER BY p.id';
        }

        if ($sql_start !== null && $results_per_page !== null)
        {
            $query .= ' LIMIT ?, ?';
            $pdo[] = $sql_start;
            $pdo[] = $results_per_page;
        }

        try {
            $stmnt = sql_pdo::run($query, $pdo);

            switch ($select_type)
            {
                case 0:
                    $result = $stmnt->fetchColumn();
                    break;
                case 1:
                default:
                    $result = $stmnt->fetchAll();
                    break;
            }
            return $result;

        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }
}
<?php
require_once('paginator_abstract.php');
require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');


class paginator_reviews extends paginator_abstract
{
    protected $rating;
    protected $product_id;

    public function __construct($page, $results_per_page, $buttons_per_bar, $rating, $product_id = false)
    {
        $this->rating     = $this->set_rating($rating);
        $this->product_id = $this->check_product_id($product_id);

        parent::__construct($page, $results_per_page, $buttons_per_bar);
    }

    protected function check_product_id($product_id)
    {
        if ($product_id == false)
        {
            return false;
        } else {
            return htmlspecialchars($product_id);
        }
    }

    protected function set_result_count()
    {
        $pdo = array();
        $product_id = $this->product_id;
        $query = "SELECT COUNT(*) FROM star_reviews WHERE hidden !=1 AND stars LIKE ?;";
        $pdo[] = $this->rating;

        if ($product_id !== false)
        {
            $query .= ' AND product = ?';
            $pdo[] = $product_id;
        }

        try {
            $result = sql_pdo::run($query, $pdo)->fetchColumn();
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    protected function set_url_query_string()
    {
        $rating = $this->rating;
        switch ($rating)
        {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
                return '&rating=' . $rating;
                break;
            default:
                return '';
        }
    }

    protected function set_url_rating($rating)
    {
        if ($rating != '')
        {
            $out = "&rating=$rating";
        } else {
            $out = '';
        }
        return $out;
    }

    protected function set_rating($rating)
    {
        if (trim($rating) !== '')
        {
            $out = (int)$rating;
        } else {
            $out = '%';
        }
        if (isset($_GET['rating']))
        {
            $out = (int)filter_var($_GET['rating'], FILTER_SANITIZE_STRING);
        }

        switch ($out)
        {
            case '%':
                break;
            default:
                if ($out < 1) {
                    $out = 1;
                }
                if ($out > 5) {
                    $out = 5;
                }
                break;
        }
        return $out;
    }

}
<?php
require_once('paginator_abstract.php');
require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');
/**
 * Created by PhpStorm.
 * User: gabriel
 * Date: 5/15/17
 * Time: 9:37 PM
 */

class paginator_reviews extends paginator_abstract
{
    protected $rating;

    public function __construct($page, $results_per_page, $buttons_per_bar, $rating)
    {

        $this->rating = $this->set_rating($rating);

        parent::__construct($page, $results_per_page, $buttons_per_bar);
    }

    protected function set_result_count()
    {
        $query = "SELECT COUNT(*) FROM star_reviews WHERE hidden !=1 AND stars LIKE ?;";

        try {
            $result = sql_pdo::run($query, [$this->rating])->fetchColumn();
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
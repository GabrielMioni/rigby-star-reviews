<?php
if (!defined('RIGBY_ROOT')) {
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * Sets $page and $results_per_page
 */
abstract class abstract_navigate
{
    /** @var int The number of results that should be navigable. */
    protected $result_count;

    /** @var int The pointer for the $page. Helps set $sql_start. */
    protected $page;

    /** @var int Default is 8. Can be a min. of 1. */
    protected $results_per_page;

    /** @var int The start for the MySQL query LIMIT that controls the slice of MySQL results requested.  */
    protected $sql_start;

    /** @var int The last page needed given the values of $result_count and $results_per_page. */
    protected $page_max;

    public function __construct($page, $results_per_page)
    {
        $this->results_per_page = $this->set_int_value($results_per_page, 8); // Defaults to 8.
        $this->result_count     = $this->set_result_count(); // Defined in concrete class.

        $this->page_max = $this->set_page_max($this->result_count, $this->results_per_page);
        $this->page     = $this->set_page($page, $this->page_max);

        $this->sql_start = $this->set_sql_start($this->page, $this->results_per_page);
    }

    /**
     * Sets a count based on variables supplied by the concrete class.
     *
     * @return int The total count for the concrete class.
     */
    abstract protected function set_result_count();

    /**
     * Returns a minimum of 1. If $int_val is whitespace or false, returns int value of $if_empty_val
     *
     * @param $int_val string|bool|int
     * @param $if_empty_val int
     * @return int
     */
    protected function set_int_value($int_val, $if_empty_val)
    {
        if (trim($int_val == '') || $int_val == false)
        {
            $int_val = $if_empty_val;
        }
        if ($int_val < 1)
        {
            $int_val = 1;
        }
        return $int_val;
    }

    /**
     * Sets the max page that should be needed to display all results given the the values of $result_count and
     * $results_per_page
     *
     * @param $result_count int
     * @param $results_per_page int
     * @return int
     */
    protected function set_page_max($result_count, $results_per_page)
    {
        if ($result_count <= 0)
        {
            return 1;
        } else {
            return ceil($result_count / $results_per_page);
        }
    }

    /**
     * Sets the abstract_navigate::$page variable, which helps set the MySQL query limit. The result can never be lower
     * than 1 or higher than the int value of $last_page.
     *
     * @param $page int The $page pointer variable being submitted, which the function validates.
     * @param $last_page int The last possible page that should exist given the number of results and results per page.
     * @return int The validated page.
     */
    protected function set_page($page, $last_page)
    {
        $page = htmlspecialchars($page);

        if (trim($page) == '' || $page == false)
        {
            $page = 1;
        }
        if ($page > $last_page)
        {
            $page = $last_page;
        }
        return $page;
    }

    /**
     * Sets the start used for the MySQL result query LIMIT. This controls the slice of results that are needed
     * for display.
     *
     * @param $page int
     * @param $reviews_per_page int
     * @return int
     */
    protected function set_sql_start($page, $reviews_per_page)
    {
        return ($page - 1) * $reviews_per_page;
    }
}

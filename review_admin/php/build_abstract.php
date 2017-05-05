<?php

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Abstract class that gets data from SQL and is used to output HTML. Examples: The table of reviews in review_admin/reviews.php and
 * 
 * The class constructor places arguments into the $this->search_array which is
 * later used for the prepared statement.
 * 
 * Used in:
 * - build_reviews_table.php
 * - build_pagination.php
 *
 * @param string  $title        Defines title
 * @param string  $name         Defines name.
 * @param string  $email        Defines email.
 * @param string  $ip           Defines IP.
 * @param integer $page         Defines start of limit in prepared statement.
 * @param integer $page_p       Defines end of limit in prepared statement
 * @param integer $date_range   Defines whether prepared statement needs to query for a single date or a range.
 * @param string  $date_single  Populated when a single date is requested.
 * @param string  $date_start   Populated when a start date range is requested.
 * @param string  $date_end     Populated when a end date range is requested.
 * @param array   $star_array   Holds array of review rating values requested.
 * @abstract
 * 
 */
class build_abstract {
    protected $search_array = array();
    protected $problems     = array();
    protected $sql_date_format;

    public function __construct($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array) {
        $like = '%';
        $this->sql_date_format = 'Y-m-d g:i:s';
        
        $this->search_array['title']        = $this->check_input($title,    $like);
        $this->search_array['name']         = $this->check_input($name,     $like);
        $this->search_array['email']        = $this->check_input($email,    $like);
        $this->search_array['ip']           = $this->check_input($ip,       $like);
        $this->search_array['page']         = $this->check_input($page, 1);
        $this->search_array['page_p']       = $this->check_input($page_p, 10);
        $this->search_array['date_range']   = $this->check_input($date_range, 4);
        $this->search_array['date_single']  = $this->check_date($date_single);
        $this->search_array['date_start']   = $this->check_date($date_start);
        $this->search_array['date_end']     = $this->check_date($date_end);
        $this->search_array['star_array']   = is_array($star_array) ? $this->check_star_array($star_array) : '';
    }

    /**
     * Build and tries to run a prepared statement based on class arguments and returns results.
     * 
     * If the prepared statement fails, $this->problems array is populated and
     * the method returns -1.
     * 
     * @param type $select_data
     * @param type $limit_bool
     * @return mixed array|integer
     * @todo Prepend $select_data to $pdo_array rather than pass $select_data directly to the query.
     * @todo Break out building prepared statement from execution.
     */
    protected function build_review_select($select_data, $limit_bool = 1) {
        $search_array = $this->search_array;
        $pdo_array    = array($search_array['title'], $search_array['name'],$search_array['email'],$search_array['ip']);
        
        $star_arr     = $search_array['star_array'];
        
        $single_date  = $search_array['date_single'];
        $start_date   = $search_array['date_start'];
        $end_date     = $search_array['date_end'];
        $page         = $search_array['page'];
        $page_p       = $search_array['page_p'];
        
        $query = "SELECT $select_data FROM star_reviews WHERE
                    title LIKE ? AND
                    name LIKE ? AND
                    email LIKE ? AND
                    ip LIKE ?";
        
        if (is_array($star_arr)) {
            $star_rev_query = '';
            foreach ($star_arr as $star_val) {
                if ($star_rev_query === '') {
                    $star_rev_query .= ' AND (';
                } else {
                    $star_rev_query .= ' OR';
                }
                $star_rev_query .= ' stars = ?';
                $pdo_array[] = $star_val;
            }
            if ($star_rev_query !== '') {
                $star_rev_query .= ')';
            }
            $query .= $star_rev_query;
        }
        $date_code = $this->determine_date_search($single_date, $start_date, $end_date);
        
        switch ($date_code) {            
            case 1:
                // single date search;
                $query .= ' AND date > ? AND date < ?';
                $pdo_array[] = $single_date;
                $pdo_array[] = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($single_date)));
                break;
            case 2:
                // range date search
                $query .= ' AND date > ? AND date < ?';
                $pdo_array[] = $start_date;
                $pdo_array[] = $end_date;
                break;
            default:
                break;
        }

        $query .= ' ORDER BY date desc';
        
        
        if ($limit_bool === 1 ) {
            $query .= $this->determine_page_limit($page, $page_p);            
        }

        try {
            $reviews = sql_pdo::run($query, $pdo_array)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $exc) {
            echo '<pre>';
            print_r($exc);
            echo '</pre>';
            $this->problems = $exc->errorInfo;
            $reviews = -1;
        }
        
        if (is_array($reviews)) {
            return $reviews;
        } else {
            return -1;
        }
    }
    
    /**
     * Look at arguments related to date range request and
     * return an integer.
     * 
     * The return integer is used to define what kind
     * of date search needs to be included in the prepared statement.
     * 
     * - used in $this->build_review_select()
     * 
     * @param mixed $single Populated if a single date is requested.
     * @param mixed $start  Populated if a start date range is requested.
     * @param mixed $end    Populated if an end date range is requested.
     * @return int
     */
    protected function determine_date_search($single, $start, $end) {
        if (($single !== '')&&($start == '')&&($end == '')) {
            // search based on single
            return 1;
        }
        elseif (($single == '')&&($start !== '')&&($end !== '')) {
            // search based on range
            return 2;
        } else {
            return 3;
        }
    }
    
    /**
     * Sets LIMIT for the prepared statement
     * 
     * Calculates what the end/start of a prepared statement's limit should
     * be based on the current page requested and how many reviews are requested
     * per page.
     * 
     * @param integer $page Requested page. Should never be lower than 1.
     * @param integer $page_per Requested reviews per page.
     * @return string eg. " LIMIT 0, 10"
     */
    protected function determine_page_limit($page, $page_per) {
        $page_start = ($page -1) * $page_per;
        $limit_query = " LIMIT $page_start, $page_per";
        return $limit_query;
    }
    
    /**
     * Checks variable and either returns it or if it's whitespace, returns
     * a different variable.
     * 
     * Used to make sure the prepared statement has a default value if none is
     * set from the class argument.
     * 
     * @param type $var Variable to be checked
     * @param type $empty_return The varible returned if $var is whitespace
     * @return mixed
     */
    protected function check_input($var, $empty_return) {
        switch (trim($var)) {
            case '':
                return $empty_return;
            default:
                return urldecode($var);
        }
    }
    
    /**
     * Accepts a date value and returns the value SQL formatted
     * date for $date 12:00am.
     * 
     * @param string $date date requested
     * @return string
     */
    protected function check_date($date) {
        if ($date === '') {
            return '';
        } else {
            $unix_midnight = strtotime(urldecode($date) .' midnight');
            return date($this->sql_date_format, $unix_midnight);
        }
    }
    
    /**
     * 
     * @param array $star_array
     * @return array
     * @todo This looks broken. Shouldn't be checking the holding array.
     */
    protected function check_star_array(array $star_array) {
        $tmp = array();
        foreach ($star_array as $star) {
            if ($star_array !== '-1') {
                $tmp[] = $star;
            }
        }
        return $tmp;
    }
}

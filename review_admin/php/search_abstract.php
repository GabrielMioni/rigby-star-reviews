<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Uses $_GET values from URL to populate class variables when building
 * prepared statements to get data from reviews.sql.
 * 
 * This abstract class makes sure child classes use the same parent constructor.
 * 
 * Used in:
 * - search_pagination.php
 * - search_reviews.php
 *
 * @abstract
 */
abstract class search_abstract {
    
    /**
     * Flags whether a search should proceed
     * 
     * @access	protected
     * @var	bool
     */
    protected $search;
    
    /** @string @access	protected*/
    protected $title;
    
    /** @string @access	protected*/
    protected $name;
    
    /** @string @access	protected*/
    protected $email;
    
    /** @string @access	protected*/
    protected $ip;
    
    /**
     * Sets LIMIT start in the prepared statement
     * 
     * @access	protected
     * @var	integer
     */
    protected $page;
    
    /**
     * Sets LIMIT end in the prepared statement
     * 
     * @access	protected
     * @var	integer
     */
    protected $page_p;
    
    /**
     * Deprecated
     * 
     * @access	protected
     * @var	string
     */
    protected $date_range;
    
    /**
     * Used if a specific date is requested from search.
     * 
     * @access	protected
     * @var	string
     */
    protected $date_single;
    
    /**
     * Defines begining of date range for prepared statement if a range
     * is requested from search
     * 
     * @access	protected
     * @var	string
     */
    protected $date_start;
    
    /**
     * Defines end of date range for prepared statement if a range
     * is requested from search
     * 
     * @access	protected
     * @var	string
     */
    protected $date_end;
    
    /**
     * Holds array of integer values for requested review ratings.
     * Passed array values by reference in $this->set_star_arr().
     * 
     * @access	protected
     * @var	array
     */
    protected $stars_array = array();
    
    public function __construct() {
        
        $this->search   = isset($_GET['search']) ? true : false;
        
        $this->title  = $this->set_get_val('title_search');
        $this->name   = $this->set_get_val('name_search');
        $this->email  = $this->set_get_val('email_search');
        $this->ip     = $this->set_get_val('ip_search');
        $this->page   = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : '';
        $this->page_p = isset($_GET['page_p']) ? ($_GET['page_p']) : '';
        $this->date_range  = $this->set_date_range();
        $this->date_single = $this->set_get_val('date_single');
        $this->date_start  = $this->set_get_val('date_start');
        $this->date_end    = $this->set_get_val('date_end');

        $this->set_star_arr($this->stars_array);
    }
    
    /**
     * Returns the value of the $_GET variable using the method's argument as
     * a pointer.
     * 
     * Eg. set_get_val('hello_get') would return the value for $_GET['hello_get'].
     * 
     * @param type $get_var_name Argument sets index pointer for $_GET values.
     * @return mixed
     */
    protected function set_get_val($get_var_name) {
        $return = isset($_GET[$get_var_name]) ? urlencode($_GET[$get_var_name]) : '';
        return $return;
    }
    
    /**
     * Sets flag to define what kind of date will be requested.
     * 
     * return = 1: A single date is specified.
     * return = 2: A range is specified.
     * return = 3: No date was specified.
     * 
     * @return integer
     */
    protected function set_date_range() {
        if (isset($_GET['date_range'])) {
            switch ($_GET['date_range']) {
                case 'date_single':
                    return 1;
                case 'date_range':
                    return 2;
                default:
                    return 3;
            }
        }
    }
    
    /**
     * Passes array by reference. The argument array if populated 
     * whenever it finds a $_GET value for a requested review rating is populated.
     * 
     * Eg. www.blah.net?star-1=1&star-3=3 would populate the argument array with
     * $star_array = array(1,3)
     * 
     * @param array $stars_array
     */
    protected function set_star_arr(array &$stars_array) {
        if (isset($_GET['star-1'])) { $stars_array[] = 1; }
        if (isset($_GET['star-2'])) { $stars_array[] = 2; }
        if (isset($_GET['star-3'])) { $stars_array[] = 3; }
        if (isset($_GET['star-4'])) { $stars_array[] = 4; }
        if (isset($_GET['star-5'])) { $stars_array[] = 5; }
    }
}
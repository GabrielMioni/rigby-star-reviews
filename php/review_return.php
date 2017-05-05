<?php

require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');
require_once('trait_check_arg.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to return review data from reviews.sql
 * 
 * This class builds a prepared statement based on class arguments.
 * The results are stored in $review_array and returned by the
 * return_review_array method.
 *
 * @param integer $rating Ratings (1-5). Can be whitespace.
 * @param string  $product Product Id. Can be whitespace.
 * @param integer $start_range Sets start for rows in the reviews.sql table.
 * @param integer $end_range Sets end for rows in the reviews.sql table.
 */
class review_return {    
    
    // Extends @method check_arg
    use check_arg;    
    
    protected $rating;
    protected $product;
    protected $start_range;
    protected $end_range;
    
    protected $review_array = array();
    
    public function __construct($rating, $product, $start_range, $end_range) {
        
        $this->rating       = $this->check_arg($rating, '%');
        $this->product      = $this->check_arg($product, '%');
        $this->start_range  = $this->check_arg($start_range, '0');
        $this->end_range    = $this->check_arg($end_range, '10');
        $this->review_array = $this->get_reviews();
    }
    
    /**
    * Sets $this->review_array with review data from prepared SQL statement.
    *
    * @return array
    */
    protected function get_reviews() {
        $pdo_array    = array();
        $pdo_array[]  = $this->rating;
        $pdo_array[]  = $this->product;
        $pdo_array[]  = $this->start_range;
        $pdo_array[]  = $this->end_range;

        $query = "SELECT * from star_reviews WHERE stars LIKE ? AND hidden != 1 AND product LIKE ? ORDER BY date desc LIMIT ?, ?;";
         
        $stmt = sql_pdo::run($query, $pdo_array);
        $tmp  = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $set_row = array();
            foreach ($row as $key => $row_val) {
                $set_row[$key] = $row_val;
            }
            $tmp[] = $set_row;
        }
        return $tmp;
    }
    
    /**
    * returns $this->review_array
    *
    * @return array
    */
    public function return_review_array() {
        return $this->review_array;
    }
}

<?php

require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to insert review data to reviews.sql
 * 
 * This class builds a prepared statement based on class arguments. The results are stored in $review_array and returned by the
 * return_review_array method.
 * 
 * All arguments are passed to an array $pdo_array, which is used for the prepared statement.
 *
 * @param string $email Sets email value.
 * @param bool $hidden Sets if the review will be hidden.
 * @param string $ip Sets ip value.
 * @param string $name Sets name value of the reviewer.
 * @param string $product Sets product code value
 * @param string $rev_text Sets review text.
 * @param integer $star_rev Sets star review value (1-5).
 * @param string $title Sets the review's title.
 */
class review_insert {
    protected $email;

    /* $pdo_array will hold all class arguments. Used in the sql prepared statement to insert reviews. */
    protected $pdo_array = array();
    
    /* $error_sql will be set as array if errors are found. Starts null */
    protected $error_sql = null;
    
    public function __construct($email, $hidden, $ip, $name, $product, $rev_txt, $star_rev, $title) {
        
        // Populate $this->pdo_array with class arguments
        $this->pdo_array[] = $email;
        $this->pdo_array[] = $hidden;
        $this->pdo_array[] = $ip;
        $this->pdo_array[] = $name;
        $this->pdo_array[] = $product;
        $this->pdo_array[] = $rev_txt;
        $this->pdo_array[] = $star_rev;
        $this->pdo_array[] = $title;

        // Try prepared statement to insert review
        $this->run_insert($this->pdo_array);
    }

    /**
     * Tries to run prepared sql statement to insert a review to the review.sql table.
     *
     * Sets $this->error_sql if the prepared statement fails.
     *
     * @param array $pdo_array
     */
    protected function run_insert(array $pdo_array) {
        
        try{
            $stmt = sql_pdo::prepare("INSERT INTO star_reviews (email,hidden,ip,name,product, cont, stars, title) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
            $stmt->execute($pdo_array);
        } catch(Exception $e) {
            $this->error_sql = $e->errorInfo[2];
        }
    }
    
    /**
    * @return mixed if errors from insert_review() are present. Else null.
    */
    public function return_error_sql()
    {
        return $this->error_sql;
    }
}


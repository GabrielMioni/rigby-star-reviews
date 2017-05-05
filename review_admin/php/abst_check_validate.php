<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Extended in concrete classes that need to perform form validation.
 * 
 * This abstract class contains methods check_validat() and check_bool()
 * which are both used to validate inputs.
 * 
 * Extended in:
 * - add_user.php
 * 
 * @abstract
 */
abstract class abst_check_validate {
    
    /**
     * Array holds all validation problems found in $this->check_validat() and
     * $this->check_bool().
     * 
     * If errors are found the array is returned as follows:
     * - $problems["problem_name"]["String for error message"];
     *
     * @access	protected
     * @var	array
     */
    protected $problems = array();

    /**
     * Sets $this->review_array with review data from prepared SQL statement.
     *
     * Meant to be used in the child class __constructor.
     *
     * @param string $var_in The input value being validated.
     * @param integer $limit Maximum allowed length for an input.
     * @param string $prob_index Index from input name.
     * @param string $validate_type Default is ''. The argument can accept a
     *                                   PHP filter ID and it will be used to test
     *                                   against $var_in. eg. FILTER_VALIDATE_EMAIL
     *
     * @return string
     */
    protected function check_validate($var_in, $limit, $prob_index, $validate_type ='') {
        $prob_msg = '';
        $var = trim($var_in);
        $msg_name = str_replace('_', ' ', $prob_index);
        
        if ($validate_type == '') {
            $check_var = true;
        } else {
            $check_var = filter_var($var, $validate_type);            
        }
        if ($check_var == FALSE) {
            $prob_msg = "The $msg_name field is not in valid format.";
        }
        if (strlen($var) > $limit) {
            $prob_msg = "The $msg_name must be under $limit characters long.";
        }
        if ($var == '') {
            $prob_msg = "The $msg_name field cannot be empty.";
        }
        if ($prob_msg !== '') {
            $this->problems[$prob_index] = $prob_msg;
            return $var;
        } else {
            return $var;
        }
    }

    /**
     * @param $bool_var bool Boolean (hopefully) input.
     * @param $prob_index string Defines name of the index used in $this->problems if validation fails.
     * @param $prob_msg string Message to be populated to the $this->problems array validation fails.
     * @return bool
     */
    protected function check_bool($bool_var, $prob_index, $prob_msg) {
        switch ($bool_var) {
            case 1:
            case 0:
                return $bool_var;
            default:
                $this->problems[$prob_index] = $prob_msg;
                return FALSE;
        }
    }
}

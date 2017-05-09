<?php

// require_once('../../config.php');

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
require_once('check_admin.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class edit_abstract
 *
 * Abstract class responsible for field validation and executing a prepared statement to update/edit reviews
 * in star_reviews.sql. This class also checks admin credentials to make sure that the Rigby user's username/password
 * pair correctly validates.
 *
 * Validation, login and SQL errors are passed to the edit_abstract::$problems array where they can be handled by the
 * concrete implementation to provide feedback to the Rigby user.
 *
 * Extended in:
 * - edit_quick.php
 * - edit_detail.php
 * - add_user.php
 */
abstract class edit_abstract {

    // Responsible for methods used in $this->validate_admin()
    use check_admin;

    /**
     * @var string|bool $_SESSION['username'] if set. Else, FALSE.
     */
    protected $username;

    /**
     * @var string|bool $_SESSION['password'] if set. Else, FALSE.
     */
    protected $password;

    /**
     * @var bool Set by $this->validate_admin();
     */
    protected $admin_valid;

    /**
     * @var string Query that will be executed in the prepared statement. Set in concrete class constructor.
     */
    protected $query;

    /**
     * @var array Holds data for prepared statement. Set in concrete class constructor via $_POST.
     */
    protected $pdo_array = array();

    /**
     * @var array Holds login, input validation and PDO errors.
     */
    protected $problems = array();

    public function __construct() {
        $this->username = isset($_SESSION['username']) ? $_SESSION['username'] : false;
        $this->password = isset($_SESSION['password']) ? $_SESSION['password'] : false;

        $this->admin_valid = $this->validate_admin($this->username, $this->password);
    }

    /**
     * Check to make sure that the current Rigby user is valid and has administrative
     * privileges.
     *
     * @param $username string Username for current Rigby user.
     * @param $password string Password for current Rigby user.
     * @return bool TRUE if the Rigby user has admin creds, Else FALSE.
     */
    protected function validate_admin($username, $password) {

        /**
         * Check if Username and Password are present.
         */
        $user_creds         = $this->chk_admin_creds($username, $password);

        /**
         * Check if the Password is valid for the Username.
         */
        $password_valid     = $this->admin_login($username, $password);

        /**
         * Check if the user has administrative privileges.
         */
        $admin_privileges   = $this->chck_admin_priv($username);

        if ($user_creds == true && $password_valid == true && $admin_privileges == true) {
            return TRUE;
        } else {
            $this->problems['login_error'] = 'Login problem.';
            return FALSE;
        }
    }

    /**
     * Validates input value. Checks to make sure input value is not whitespace, against a max character limit
     * and an optional Filter ID which is passed to filter_var().
     *
     * @param   $var_in mixed         The input value to be checked.
     * @param   $limit integer        The maximum character length accepted for the input.
     * @param   $prob_index string    The index for $this->problems that should be populated if input value fails validation.
     * @param   $blank_ok integer     If 0 $var_in can be whitespace, else it must not be whitespace.
     * @param   string $filter_id Accepts a validation filter ID that will be used to validate $var_in if present.
     * @return string mixed
     */
    protected function validate_input($var_in, $limit, $prob_index, $blank_ok, $filter_id ='') {

        // $prob_msg is set if $var_in fails validation. There are 3 different validation checks.
        // If more than one validation check is failed, $prob_msg will be populated with the latest check
        $prob_msg = '';

        // Trim whitespace from $var_in.
        $var = trim($var_in);

        // Set field name for display to the Rigby user.
        $index_name = str_replace('_', ' ', $prob_index);

        // Check if a $filter_id was passed to the method. If not, set $check_var = TRUE.
        // Else validate $var using the $filter_id.
        switch ($filter_id) {
            case '':
                $check_var = TRUE;
                break;
            default:
                $check_var = filter_var($var, $filter_id);
                break;
        }

        // Check if $check_var passed validation
        if ($check_var == FALSE) {
            $prob_msg = "The $index_name field is not in valid format.";
        }

        // Check if $var doesn't exceed character limit.
        if (strlen($var) > $limit) {
            $prob_msg = "The $index_name must be under $limit characters long.";
        }

        // If $var is whitespace and whitespace is not allowed, set an error message
        if ($var == '' && $blank_ok == 0) {
            $prob_msg = "The $index_name field cannot be empty.";
        }
        // If $var is whitespace and whitespace is not allowed, set $var = NULL
        if ($var == '' && $blank_ok == 1) {
            $var = NULL;
        }

        // If $prob_msg has been populated, set $edit_abstract::$problems.
        if ($prob_msg !== '') {
            $this->problems[$prob_index] = $prob_msg;
        }
        // Return var. This will be used to display in the source field input if form validation fails.
        return $var;
    }

    /**
     * Validates input is a date.
     *
     * False populates $this->problems array.
     *
     * @param $date
     * @return bool|false|string
     */
    protected function validate_date($date) {
        if (false === strtotime($date)) {
            $this->problems['date'] = "Date is invalid";
            return FALSE;
        } else {
            return date('Y-m-d H:i:s', strtotime($date));
        }
    }

    /**
     * Checks if input is an integer valued 0-1.
     *
     * If $hidden = 0 or 1, return $hidden. Else, return FALSE and populate $this->problems.
     *
     * @param $hidden
     * @return integer|bool
     */
    protected function validate_hidden($hidden) {
        switch ($hidden) {
            case 1:
            case 0:
                return $hidden;
            default:
                $this->problems['hidden'] = 'Hidden value is invalid.';
                return FALSE;
        }
    }

    /**
     * Validates input is an integer valued 1-5.
     *
     * If $stars is an integer 1-5, return $stars. Else return FALSE and populate $this->problems.
     *
     * @param $stars integer
     * @return integer|bool
     */
    protected function validate_stars($stars) {
        switch ($stars) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return $stars;
            default:
                $this->problems['stars'] = 'Stars value is invalid.';
                return FALSE;
        }
    }

    /**
     * Validates input is a number.
     *
     * False populates $this->problems array.
     *
     * @param $num integer
     * @return bool|int
     */
    protected function validate_numeric($num) {
        $check_int = intval($num);
        switch ($check_int) {
            case true:
                return intval($num);
            default:
                $this->problems['id'] = 'ID is invalid.';
                return FALSE;
        }
    }

    /**
     * Checks if validation errors were found. If not, calls edit_abstract::update_review() to
     * process the PDO.
     *
     * @param $query string The PDO Query. Defined in concrete classes' constructor.
     * @param array $pdo_array Array of validated field inputs. Defined in concrete classes' constructor.
     * @param array $problems Array of validation errors.
     */
    protected function try_update($query, array $pdo_array, array $problems) {

        if (empty($problems)) {
            $run_pdo = true;
        } else {
            $run_pdo = false;
        }

        switch ($run_pdo) {
            case TRUE:
                $this->update_review($query, $pdo_array);
                break;
            case FALSE:
                break;
            default:
                break;
        }
    }

    /**
     * Build and execute the prepared statement to update the review.
     *
     * If the update to review.sql fails, populate $this->problems.
     *
     * @param $query
     * @param array $pdo_array Holds values passed to the prepared statement.
     * @return mixed
     */
    protected function update_review($query, array $pdo_array) {

        // Initialize array to hold values of $pdo_array without indexes.
        $pdo = array();

        // Populate $pdo array.
        foreach ($pdo_array as $pdo_elm) {
            $pdo[] = $pdo_elm;
        }

        try {
            sql_pdo::run($query, $pdo);
        } catch (Exception $e) {
            $this->problems['update_error'] = $e->getMessage();
        }
    }

    /**
     * Checks if there are no validation errors. If none are present, set success message. If validation errors were found,
     * set error messages in $_SESSION.
     *
     * @param array $problems Array of validation errors.
     */
    protected function post_processing(array $problems) {

        // Check if last submit found validation or PDO errors.
        if (empty($problems)) {
            $edit_successful = TRUE;
        } else {
            $edit_successful = FALSE;
        }

        // If there were no errors, set a success message. Else, set error messages.
        switch ($edit_successful) {
            case TRUE:
                $this->set_success_message();
                break;
            case FALSE:
                $this->set_error_messages($problems);
                break;
        }
    }

    /**
     * Abstract method to set a success message.
     *
     * - In edit_detail::set_success_message(), method sets $_SESSION success message.
     * - In edit_quick::set_success_message(), method sets a flag to let an Ajax call know the update was processed.
     *
     * @return mixed
     */
    abstract protected function set_success_message();

    /**
     * Abstract method to set_error messages.
     *
     * - In edit_detail::set_success_message(), method sets $_SESSION error messages.
     * - In edit_quick::set_success_message(), method builds a json encoded string with error message data and returns
     * the string to the Ajax call.
     *
     * @param array $problems
     * @return mixed
     */
    abstract protected function set_error_messages(array $problems);

}
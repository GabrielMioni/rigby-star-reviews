<?php

//require_once('../../review_login/define.php');
require_once(RIGBY_ROOT . '/review_login/define.php');
require_once('edit_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 *
 * Builds a prepared statement to add new users for admin login.
 *
 * Prepared statement uses $_POST data to populate an array, which is passed to the prepared statement.
 *
 * Uses the check_admin trait to make sure the person submitting $_POST data has the credentials to allow admin
 * access.
 *
 * This class is used in:
 * -add_user_act.php
 *
 */
//class add_user extends abst_check_validate{
class add_user extends edit_abstract
{
    /**
     * Holds the response sent to the Ajax call.
     *
     * Set in edit_abstract::post_processing(). If update is successful, $ajax_reply will be set to '1'. If it fails,
     * $ajax_reply will be set to a json_encoded string that holds data for which fields failed validation.
     *
     * @var null
     */
    protected $ajax_reply = NULL;

    public function __construct($installer_admin = null)
    {
        parent::__construct();

        /**
         * Bypass admin check if class is being called by the installer.
         */
        if ($installer_admin == true)
        {
            unset($this->problems['login_error']);
            unset($this->problems['admin_creds']);
        }

        $new_name           = isset($_POST['new_name'])  ? $this->validate_input($_POST['new_name'],  30, 'name',                0)                        : '';
        $email              = isset($_POST['new_email']) ? $this->validate_input($_POST['new_email'], 50, 'email',               0, FILTER_VALIDATE_EMAIL) : '';
        $password_set       = isset($_POST['pswd_set'])  ? $this->validate_input($_POST['pswd_set'],  20, 'password',            0)                        : '';
        $password_confirm   = isset($_POST['pswd_con'])  ? $this->validate_input($_POST['pswd_con'],  20, 'confirm',             0)                        : '';
        $admin              = isset($_POST['priv'])      ? $this->validate_hidden($_POST['priv'])                                                          : '';

        $this->pdo_array['name']  = $new_name;
        $this->pdo_array['email'] = $email;
        $this->pdo_array['hash']  = password_hash($password_set, PASSWORD_DEFAULT);
        $this->pdo_array['admin'] = $admin;

        $query = "INSERT INTO users (username, email, hash, admin, reg_date) VALUES (?,?,?,?, NOW());";

        $this->check_existing_users($this->pdo_array['name']);
        $this->compare_pswd($password_set, $password_confirm);

        $this->try_update($query, $this->pdo_array, $this->problems);
        $this->post_processing($this->problems);

    }

    /**
     * Make sure the username being submitted doesn't already exist.
     *
     * If validation fails, populate $this->problems.
     *
     * @param $user_name string New User Name the current Rigby user would like to add.
     */
    protected function check_existing_users($user_name)
    {
        $query = "SELECT * FROM users WHERE username = ?";
        
        try {
            $results = sql_pdo::run($query, [$user_name])->fetchAll();
        } catch (Exception $exc) {
            $results = array();
            $this->problems['sql_err'] = $exc->getMessage();
        }
        if (!empty($results)) {
            $this->problems['name'] = 'Username already exists.';
        }
        if (trim($user_name) == '') {
            $this->problems['name'] = 'Username cannot be blank';
        }
    }

    /**
     * The new user submit form requires password verification. $POST[pswd_set] and $_POST['pswd_con'] must match and
     * neither can be empty.
     *
     * If validation fails, populate $this->problems.
     *
     * @param $pswd_set     string The password the Rigby user is trying to set for the user.
     * @param $pswd_confirm string Confirmation of the password.
     */
    protected function compare_pswd($pswd_set, $pswd_confirm)
    {
        // Check that passwords match
        $compare_state = strcmp($pswd_set, $pswd_confirm);

        switch ($compare_state) {
            case 0:
                break;
            default:
                $this->problems['password'] = 'Password does not match.';
                break;
        }
    }

    protected function set_success_message()
    {
        $this->ajax_reply = 1;
    }

    protected function set_error_messages(array $problems) {

        $this->ajax_reply = json_encode($problems, TRUE);
    }

/*
    protected function set_error_messages(array $problems) {

        // Initialize array to hold $keys from $problems array.
        $ajax_errors = array();

        // Populate $ajax_errors with $problems keys.
        foreach ($problems as $key => $problem) {
            $ajax_errors[] = $key;
        }
        // Set edit_quick::$ajax_reply with JSON encoded string from $ajax_errors.
        $json_errors = json_encode($ajax_errors);
        $this->ajax_reply = $json_errors;
    }
*/

    /**
     * Public access for add_user.php::$ajax_reply
     * @return integer|string Returns 1 if update was successful, Else returns a json_encoded string.
     */
    public function get_ajax_reply()
    {
        return $this->ajax_reply;
    }

}

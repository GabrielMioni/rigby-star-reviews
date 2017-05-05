<?php

require_once('../config.php');

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

require_once('login_user.php');
require_once('login_remember.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */


/**
 * Logs the Rigby user in or provides error feedback if login fails.
 *
 * This class checks Rigby user creds and sets $_SESSION with the username and password data if login is
 * successful. Those user creds are checked every time a Rigby user accesses an admin page. The check is
 * performed by login_check.php (which will re-direct to ../review_login/index.php if the Rigby user
 * credentials are bad).
 *
 * @uses login_remember
 *
 */
class login_act
{

    /**
     * @var mixed|string Holds the Username provided by the Rigby user.
     */
    protected $input_username;

    /**
     * @var mixed|string Holds the Password provided by the Rigby user.
     */
    protected $input_password;

    /**
     * @var int|string Set if the Rigby user chooses to 'stay logged in'.
     */
    protected $input_remember;


    protected $db_username;
    protected $db_password;

    protected $error = null;
    protected $pswd_valid = null;

    public function __construct()
    {
        $this->input_username = isset($_POST['user_name']) ? $this->filter('user_name') : '';
        $this->input_password = isset($_POST['pswd']) ? $this->filter('pswd') : '';
        $this->input_remember = isset($_POST['set_remember']) ? 1 : '';
        
        $this->error = $this->assign_error($this->input_username, $this->input_password);

        $this->try_login($this->input_username, $this->input_password, $this->error);
    }

    /**
     * Filters out whitespace and strips tags.
     *
     * @param $var mixed
     * @return mixed
     */
    protected function filter($var)
    {
        //Remove HTML tags.
        $out_var = filter_input(INPUT_POST, $var, FILTER_SANITIZE_STRING);

        // Remove whitespace and return the output.
        return preg_replace('/\s+/', '', $out_var);
    }

    /**
     * Reviews $input_username and $input_password and returns an error code. The error code is used to
     * later set an error message.
     *
     * If everything is correct
     *
     * @param $input_username string Username provided by Rigby user.
     * @param $input_password string Username provided by Rigby user.
     *
     * @return null|integer If $input_username and $input_password are valid, return NULL. Else, return integer.
     */
    protected function assign_error($input_username, $input_password)
    {
        $validate_username = true;
        $validate_password = true;

        $error_code = null;
        
        if ($input_username === '') { $validate_username = false; }
        if ($input_password === '') { $validate_password = false; }
        
        if ($input_username === -1) { $validate_username = false; }
        if ($input_password === -1) { $validate_password = false; }

        // Username: EMPTY and Password: EMPTY
        if (($validate_username === false)&&($validate_password === false)) {
            $error_code = 1;
        }
        // Username: FULL and Password: EMPTY
        if (($validate_username === true)&&($validate_password === false)) {
            $error_code = 2;
        }
        // Username: EMPTY and Password: FULL
        if (($validate_username === false)&&($validate_password === true)) {
            $error_code = 3;
        }
        // Username: FULL and Password: FULL
        if (($validate_username === true)&&($validate_password === true)) {
            $error_code = null;
        }
        return $error_code;
    }

    /**
     * Checks the $error variable set by login_act::assign_error() and either sets an appropriate error message or
     * checks the Rigby user's credentials.
     *
     * @param $username string Passed to login_act::check_user(). Username provided by the Rigby user.
     * @param $password string Passed to login_act::check_user(). Password provided by the Rigby user.
     * @param $error null|integer
     */
    protected function try_login($username, $password, $error)
    {
        switch ($error)
        {
            case null:
                $this->check_users($username, $password);
                break;
            default:
                $this->login_failed($this->error);
                break;
        }
    }

    /**
     * Check if the $username provided by the Rigby user exists. If it does, try to login the user by calling
     * the class login_user.
     *
     * Called in login_act::try_login()
     *
     * @param $username string Username provided by the Rigby user.
     * @param $password string Password provided by the Rigby user.
     */
    protected function check_users($username, $password)
    {

        // Check if the $username exists in users2.sql.
        $chk_name = sql_pdo::run("SELECT username FROM users2 WHERE username=? LIMIT 1", [$username])->fetchColumn();

        // If the $username exists, check the $username and $password credentials by calling the class login_user.
        if (!empty($chk_name)) {
            $try_login = new login_user($username, $password);
            $password_check  = $try_login->return_pswd_chk();

            // If $password_check is TRUE, log the user in. Else, login fails.
            $this->check_pswd($password_check);
        } else {
            // If the username does not exist, login fails.
            $this->error = 4;
            $this->login_failed($this->error);
        }
    }

    /**
     * Checks the boolean value login_user::return_pswd_chk().
     *
     * If TRUE, the password for the username successfully validated when hashed and compared to the hashed password
     * in in the user record in users2.sql. If FALSE, set an error code and login fails.
     *
     * @param $pswd_chk bool Value from login_user::return_pswd_chk();
     */
    protected function check_pswd($pswd_chk)
    {
        switch ($pswd_chk) {
            case TRUE:
                $this->login();
                break;
            case FALSE:
                $this->error = 5;
                $this->login_failed($this->error);
                break;
            default:
                $this->login_failed($this->error);
                break;
        }
    }

    /**
     * Sets $_SESSION data with user credentials that will be used to validate the Rigby user on each page.
     *
     * When a log in is successful, this method sets $_SESSION with the username and password credentials provided
     * by the Rigby user. Those variables will be checked on each administrative page and validated by the
     * login_check class.
     *
     * Redirect the Rigby User to ../review_admin/
     *
     * @uses login_remember to set the users2.sql table with the cookie token
     */
    protected function login()
    {
        $input_remember = $this->input_remember;
        if ($input_remember === 1) {
            new login_remember($this->input_username, 86400);
        }
        session_start();
        $_SESSION['remember'] = $this->input_remember;
        $_SESSION['username'] = $this->input_username;
        $_SESSION['password'] = $this->input_password;
        header('Location: ../review_admin/');
    }

    /**
     * Sets $_SESSION with an appropriate error message, based on the reason the error failed. The method
     * can be changed for less verbosity for better security.
     *
     * @param $error_code integer Passed to a switch statement that sets an error message.
     */
    protected function login_failed($error_code)
    {
        session_start();

        $_SESSION['entered_name'] = $this->input_username;
        $_SESSION['error_code']   = $error_code;
        
        /*  1 Username: EMPTY / Password: EMPTY
            2 Username: FULL  / Password: EMPTY
            3 Username: EMPTY / Password: FULL
            4 No user found
            5 Password invalid
            NULL - Everything is good */
        
        $error_msg = '<b>ERROR:</b> ';
        
        switch ($error_code) {
            case 1:
                $error_msg .= "Username and Password are both required.";
                break;
            case 2:
                $error_msg .= "Password is required.";
                break;
            case 3:
                $error_msg .= "Username is required.";
                break;
            case 4:
                $error_msg .= "'$this->input_username' is not a valid Username.";
                break;
            case 5:
                $error_msg .= "The password entered for '$this->input_username' is incorrect.";
                break;
            default:
                $error_msg .= "Bad login. Please try again";
                break;
        }
        $_SESSION['login_error'] = $error_msg;
        header('Location: ../review_login/');
    }
    
}

if (isset($_POST['login'])) {
    new login_act();
}



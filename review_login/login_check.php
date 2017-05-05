<?php

require_once(RIGBY_ROOT . '/php/hash_equals.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');

require_once('define.php');
require_once('login_user.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Validate the user credentials provided by the Rigby user when they logged in.
 *
 * This class collects user credentials set in $_SESSION by the login_user class, and checks to make sure the credentials
 * are valid.
 *
 * Credentials the class reviews include the username, password and a cookie token set if the Rigby user chose to
 * 'stay logged in' when they last logged in [and if they have not since chosen to log out].
 *
 * If the credentials are valid the Rigby user can remain on the page. If not, they're redirected to ../review_login/ and
 * provided with an error message.
 *
 * Used in:
 * - ../review_admin/index.php
 * - ../review_admin/reviews.php
 * - ../review_admin/users.php
 */
class login_check
{
    /**
     * @var string|bool Set by $_SESSION['username']. The username provided by the Rigby user.
     */
    protected $username;

    /**
     * @var string|bool Set by $_SESSION['password']. The password provided by the Rigby user.
     */
    protected $password;

    /**
     * @var string|bool Set by $_COOKIE['star_rev']. The cookie is created if the Rigby user had previously chosen
     * to 'stay logged in.' Cookie is created in login_remember.php
     */
    protected $cookie;

    public function __construct() {
        $this->logged_in = null;
        
        $this->username = isset($_SESSION['username']) ? $_SESSION['username'] : FALSE;
        $this->password = isset($_SESSION['password']) ? $_SESSION['password'] : FALSE;
        $this->cookie   = isset($_COOKIE['rigby'])  ? $_COOKIE['star_rev']  : FALSE;
        
        $this->start_login_check($this->username, $this->password, $this->cookie);
    }

    /**
     * Validates the Rigby user's login by checking either username and password credentials or seeing if a valid
     * cookie token is set.
     *
     * @param $username string|bool The username provided by the Rigby user.
     * @param $password string|bool The password provided by the Rigby user.
     * @param $cookie string|bool A cookie token set by $_COOKIE['star_rev']
     */
    protected function start_login_check($username, $password, $cookie)
    {
        // If both $password and $username have been set, check user credentials.
        if ($username !== FALSE && $password !== FALSE) {
            $credential_check = $this->check_user_pass($username, $password);
        } else {
            $credential_check = FALSE;
        }

        // If $cookie is set, check cookie token
        if ($cookie !== FALSE) {
            $cookie_check = $this->check_logged_cookie($cookie);
        } else {
            $cookie_check = FALSE;
        }

        // If either user credentials or the cookie token were valid, login is successful. Else, Login fails.
        if ($credential_check == TRUE || $cookie_check == TRUE) {
            $login_state = TRUE;
        } else {
            $login_state = FALSE;
        }

        switch ($login_state) {
            case FALSE:
                $this->login_failed();
                break;
            default:
                break;
        }
    }

    /**
     * Checks a cookie token and compares it to the token stored in users2.sql.
     *
     * Called in login_check::start_login_check()
     *
     * @param $cookie string The cookie token set by $_COOKIE['star_rev']
     * @return bool If token is valid, TRUE. Else, FALSE
     */
    function check_logged_cookie($cookie)
    {
        if ($cookie !== FALSE) {
            // Split the $cookie token up into $user, $token, $mac
            list ($user, $token, $mac) = explode(':', $cookie);

            // Hash the reconstructed token.
            $hash_from_token = hash_hmac('sha256', $user . ':' . $token, SECRET_KEY);

            // Compare hash with $mac.
            if (!hash_equals($hash_from_token, $mac)) {
                return FALSE;
            }

            // Get the currently set token for the Rigby user from users2.sql.
            $user_token = $this->fetch_token($user);

            // Compare stored token with token from the cookie.
            if (hash_equals($user_token, $token)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Performs a prepared statement to get the current token set for the Rigby user in users2.sql
     *
     * Called in login_check::check_logged_cookie()
     *
     * @param $username string Username extracted from the cookie.
     * @return string Returns token if one is found. Else, returns NULL.
     */
    protected function fetch_token($username)
    {
        try {
            $row = sql_pdo::run("SELECT * FROM users WHERE username=? LIMIT 1", [$username])->fetch();
        } catch (Exception $exception) {
            $row = FALSE;
        }
        
        if (!empty($row) || $row !== FALSE) {
            $token = $row['token'];
        } else {
            $token = NULL;
        }
        return $token;
    }


    /**
     * Checks to see if the Username provided by the Rigby user exists. If so, check if the credentials are
     * correct by calling a login_user class object.
     *
     * @param $username string|bool Username provided by the Rigby user.
     * @param $password string|bool Password provided by the Rigby user.
     * @return boolean If user credentials successfully validate, return True. Else, False.
     */
    protected function check_user_pass($username, $password)
    {
//        $check_value = null;
        
        try
        {
            $row = sql_pdo::run("SELECT * FROM users2 WHERE username=? LIMIT 1", [$username])->fetch();
        } catch (Exception $e) {
            $row = null;
        }
        
        if (!empty($row) || $row !== null)
        {
            $try_login = new login_user($username, $password);
            $password_check  = $try_login->return_pswd_chk();

            return $password_check;
        } else {
            $password_check = false;
        }

        return $password_check;
    }

    /**
     * Sets a $_SESSION message to let the Rigby user know they failed to log in.
     * Redirects to ../reviews_login.
     */
    protected function login_failed()
    {
        $_SESSION['logout'] = "You have been logged out.";
        header('Location: ../review_login/');
    }
}

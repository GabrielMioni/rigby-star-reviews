<?php

require_once("build_token.php");
require_once("define.php");
require_once('../php/sql_pdo/sql_define.php');
require_once('../php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Creates and sets a cookie token that allows the Rigby user to 'stay logged in.'
 *
 * The cookie is created by creating a random character string, joining that to the Rigby user's username
 * and hashing the resulting string with a SECRETE_KEY defined in define.php.
 *
 * The cookie stored in the browser will be: $username:$token:$mac. The $token is stored on users2.sql for the Rigby
 * user.
 *
 * Rigby validates the cookie in login_check::check_logged_cookie() using the SECRET_KEY defined in define.php.
 *
 * Used in:
 * - login_act.php
 *
 */
class login_remember
{
    protected $username;
    protected $cookie_expiration;
    protected $token;
    protected $cookie;

    protected $problems;

    /**
     * login_remember constructor.
     * @param $username     string  The username for the Rigby user.
     * @param $cookie_exp   int     Number of seconds the cookie will be valid for.
     */
    public function __construct($username, $cookie_exp)
    {
        $this->username   = $username;
        $this->cookie_expiration = $cookie_exp;

        $this->token      = $this->create_new_token();
        $this->cookie     = $this->build_cookie($this->username, $this->token);

        $this->store_token($this->username, $this->token);
        $this->set_local_cookie();
    }

    /**
     * Creates and returns a token string by creating a build_token class object.
     *
     * @uses build_token::return_token();
     * @return string Returns a string of random upper/lower case alhabets and numbers.
     */
    protected function create_new_token()
    {
        $build_rand = new build_token(20,20,20);
        return $build_rand->return_token();
    }

    /**
     * Creates the cookie that will be stored locally on the Rigby user's browser.
     *
     * $username and $token are joined with a ':' between them. That string is hashed using SECRET_KEY {@see define.php}
     *
     * The resulting string is appended to the $cookie string.
     *
     * @param $username string The Rigby user's username.
     * @param $token    string The token created by login_remember::create_new_token();
     * @return string   string Returns a cookie to be stored on the Rigby user's browser.
     */
    protected function build_cookie($username, $token)
    {
        $cookie  = $username . ':' . $token;
        $mac     = hash_hmac('sha256', $cookie, SECRET_KEY);
        $cookie .= ':'.$mac;
        return $cookie;
    }

    /**
     * Tries to update the users2.sql record for the $username with the $token.
     *
     * @todo Add some error handling.
     * @param $username string The Rigby user's username.
     * @param $token    string The token created by login_remember::create_new_token();
     */
    protected function store_token($username, $token)
    {
        $query = "UPDATE users2 SET token=?,token_exp=NOW() WHERE username=?";

        try {
            $stmt  = sql_pdo::prepare($query);
            $stmt->execute([$token, $username]);
        } catch (Exception $exception) {
            $this->problems[] = $exception->getMessage();
        }
    }

    /**
     * Sets the cookie on the Rigby user's browser.
     *
     * The expiration date is set using login_remember::cookie_expiration.
     */
    protected function set_local_cookie()
    {
        $cookie = $this->cookie;
        setcookie('rigby', $cookie, time()+$this->cookie_expiration,'/');
    }
}

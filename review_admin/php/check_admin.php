<?php

require_once(RIGBY_ROOT . '/review_login/login_user.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This trait is used to confirm user credentials for Rigby.
 *
 * Used in:
 * - review_admin/users.php
 * - review_admin/php/add_user.php
 * - review_admin/php/build_review_table.php
 * - review_admin/php/quick_edit.php
 */
trait check_admin {

    /**
     * Checks if username/password are present and valid.
     *
     * Check fails if either $username or $password aren't present or the credentials
     * are invalid.
     *
     * @param $username string Usually supplied through $_SESSION['username'].
     * @param $password string Usually supplied through $_SESSION['password].
     * @return bool Returns TRUE if successful, FALSE if fail
     */
    protected function chk_admin_creds($username, $password) {
        
        if ($username == false || $password == false) {
            $username == false ? $this->problems['admin_creds'] = 'Invalid admin.' : '';
            $password == false ? $this->problems['admin_creds'] = 'Invalid admin.' : '';
            $pswd_chk = false;
        } else {
            $pswd_chk = $this->admin_login($username, $password);
        }
        return $pswd_chk;
    }

    /**
     * Creates login_user object, which compares a password hash based on the current
     * user password with the hash stored in users.sql
     *
     * @param $username
     * @param $password
     * @return bool FALSE if check fails, TRUE if check succeeds.
     */
    protected function admin_login($username, $password) {
        $chk_login = new login_user($username, $password);
        return $chk_login->return_pswd_chk();
    }

    /**
     * Runs a prepared statement to see if the current user has admin privileges.
     *
     * This doesn't do any work to confirm user credentials are valid. It just returns
     * info about the data held in the users.sql admin column for the username.
     *
     * @param $username
     * @return bool
     */
    protected function chck_admin_priv($username) {
        $query = "SELECT admin from users2 WHERE username=?;";
        $result = sql_pdo::run($query, [$username])->fetchColumn();
        switch ($result) {
            case 1:
                return true;
            default:
                return false;
        }
    }
}
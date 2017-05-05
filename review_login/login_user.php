<?php

require_once('../config.php');

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Collects the password hash stored for a given $username stored on users2.sql and compares it to the
 * $password provided by the Rigby user.
 *
 * The result of the validation is returned by login_user::return_pswd_chk();
 *
 * Used in:
 * - login_act.php
 * - login_check.php
 * - create_user.php
 */
class login_user
{
    /**
     * @var string $username provided by the Rigby user.
     */
    protected $username;

    /**
     * @var string $password provided by the Rigby user.
     */
    protected $password;

    /**
     * @var bool|string Stores the result of PDO requesting the hash password for the $user. Can be whitespace if no data is found.
     */
    protected $db_hash;

    /**
     * @var bool If hash and password are validated, TRUE. Else, FALSE.
     */
    protected $password_check;

    /**
     * @var array Holds SQL errors.
     */
    protected $problems = array();

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->db_hash  = $this->get_db_hash($this->username);
        $this->password_check = password_verify($this->password, $this->db_hash);
    }

    /**
     * Try to get the password hash stored on users2.sql for the $username provided.
     *
     * @param $username string Username provided by the Rigby user.
     * @return bool|string
     */
    protected function get_db_hash($username)
    {
        $query = "SELECT hash from users2 WHERE username = ? LIMIT 1";
        try {
            $result = sql_pdo::run($query, [$username])->fetchColumn();
        } catch (Exception $exc) {
            $result = false;
            $this->problems['sql'] = $exc->getMessage();
        }
        return $result;
    }

    /**
     * Public access for the results of the password/hash validation.
     *
     * @return bool TRUE if password/hash passed validation. Else, FALSE.
     */
    public function return_pswd_chk()
    {
        return $this->password_check;
    }

}

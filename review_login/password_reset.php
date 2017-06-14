<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once('password_reset_abstract.php');
require_once(RIGBY_ROOT . '/php/set_rigby_home_url.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

class password_reset extends password_reset_abstract {

    public function __construct()
    {
        $this->process_request();
    }

    protected function process_request()
    {
        $generic_fail = 'Your request couldn\'t be completed. Please try again later';

        $submit   = isset($_POST['submit']) ? true : false;
        $reset_id = $this->check_post('reset_id');

        if ($submit == false || $reset_id == false)
        {
            $this->send_to_login();
            return false;
        }

        $pswd_new = $this->check_post('password');
        $pswd_con = $this->check_post('confirm');

        if ($pswd_new == false || $pswd_con == false)
        {
            if ($pswd_new == false && $pswd_con == false)
            {
                $this->set_message(false, 'You must provide a new password and confirm it by re-typing the same password in the confirmation field.');
            }
            elseif ($pswd_new == false)
            {
                $this->set_message(false, 'The \'Password\' field cannot be blank.');

            }
            elseif ($pswd_con == false)
            {
                $this->set_message(false, 'The \'Confirmation\' field cannot be blank.');

            }

            $this->send_to_forgot_password($reset_id);
        }

        $pswds_match = $this->validate_new_password($pswd_new, $pswd_con);

        if ($pswds_match == false)
        {
            $this->set_message(false, 'Passwords do not match');
            $this->send_to_forgot_password($reset_id);
        }

        $user_data = $this->get_user_data($reset_id);

//        $reset_flag = null;

        switch ($user_data)
        {
            case -1:    // The PDO to get $user_data has failed.
                $this->set_message(false, $generic_fail);
                $this->send_to_forgot_password($reset_id);
                break;
            case false: // Couldn't find any data associated with $reset_id.
                break;
            default:    // Everything is okay.
                break;
        }

        $try_update = $this->try_update_new_password($user_data, $pswd_new);


        switch ($try_update)
        {
            case true: // Success! The password hash has been updated in users.sql
                $this->set_message(true, 'Password has been changed! Please login using your new password');
                $this->send_to_login();
                break;
            case false: // The PDO failed
                $this->set_message(true, $generic_fail);
                $this->send_to_forgot_password($reset_id);
                break;
            case -1: // The Reset ID token is expired.
                $this->set_message(false, 'Your reset request has expired.');
                $this->send_to_forgot_password($reset_id);
                break;
        }
    }

    protected function check_get($get_index)
    {
        if (!isset($_GET[$get_index]))
        {
            return false;
        }

        if (trim($_GET[$get_index]) == '')
        {
            return false;
        }

        return htmlspecialchars($_GET[$get_index]);
    }

    protected function check_post($post_index)
    {
        if (!isset($_POST[$post_index]))
        {
            return false;
        }
        if (trim($_POST[$post_index]) == '')
        {
            return false;
        }

        return htmlspecialchars($_POST[$post_index]);
    }

    protected function validate_new_password($password_new, $password_con)
    {
        if ($password_con == false && $password_con == false)
        {
            return false;
        }
        $compare = strcmp($password_new, $password_con) == 0 ? true : false;

        return $compare;
    }

    protected function get_user_data($reset_id)
    {
        if ($reset_id == false)
        {
            return false;
        }

        try {
            $query = 'SELECT id, reset_exp FROM users WHERE reset_id = ?';
            $result = sql_pdo::run($query, [$reset_id])->fetchAll();

            return $result[0];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $result = -1;
        }

        if (empty($result))
        {
            return false;
        }

        return $result;
    }

    protected function try_update_new_password($user_data, $password)
    {
        if (!is_array($user_data))
        {
            return false;
        }

        $id  = $user_data['id'];
        $exp = $user_data['reset_exp'];

        if (time() > strtotime($exp))
        {
            // Reset ID is expired.
            return -1;
        }

        $pswd_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
//            $query = "UPDATE users SET hash = ? WHERE id = ?";
            $query = "UPDATE users SET hash = ?, reset_id = '', reset_exp = '' WHERE id = ?";
            sql_pdo::run($query, [$pswd_hash, $id]);
            return true;
        } catch (PDOException $e)
        {
            error_log($e->getMessage());
            return false;
        }
    }


    protected function send_to_forgot_password($reset_id = null)
    {
        $url  = set_rigby_home_url();
        if ($reset_id == null)
        {
            $url .= '/review_login/forgot_password.php';
        } else {
            $url .= '/review_login/forgot_password.php?' . htmlspecialchars($reset_id);
        }

        header('Location: ' . $url);
        exit;
    }

    protected function send_to_login()
    {
        $url = set_rigby_home_url();
        $url .= '/review_login';

        header('Location: ' . $url);
        exit;
    }

}
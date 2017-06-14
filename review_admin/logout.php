<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class logout
 *
 * Simple class that destroys the Rigby user's $_SESSION and then redirects to ../review_login.
 *
 * A 'logout' message is set.
 */
class logout {
    protected $login_page;

    public function __construct() {
        $this->login_page = "../review_login/";

    }
    public function destroy_sess() {
        session_start();
        session_destroy();
    }
    public function redirect() {
        session_start();
//        $_SESSION['logout_msg'] = 1;
        $this->set_message(true, 'You have been logged out');
        $login_page = $this->login_page;
        header('Location: '.$login_page);        
    }

    /**
     * Builds an array with data that's used to set a response message that displays for the user on forgot_password.php
     * The array is JSON encoded and set as a element at $_SESSION['pswd_reset_result].
     *
     * @param $status bool  Sets the first element in the array. False = 0, True = 1. 0 is for displaying 'error' messages,
     *                      1 is for 'good' messages.
     * @param $msg string   The message that should be displayed to the user.
     */
    protected function set_message($status, $msg)
    {
        $json_array = array();
        $json_array[] = $status == true ? 1 : 0;
        $json_array[] = $msg;

        $json_string = json_encode($json_array);

        $_SESSION['pswd_reset_result'] = $json_string;
    }
}

if (isset($_GET['logout'])) {
    $logout = new logout();
    
    $logout->destroy_sess();
    $logout->redirect();
}
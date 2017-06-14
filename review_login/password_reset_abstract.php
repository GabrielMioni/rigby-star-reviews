<?php


abstract class password_reset_abstract
{
    abstract protected function process_request();

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

    /**
     * Sends the user back to the forgot_password.php page where any messages that have been set at
     * $_SESSION['pswd_reset_result] are displayed.
     */
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
}
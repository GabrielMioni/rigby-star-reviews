<?php

/**
 * Formats and displays JSON encoded responses from PHP classes that need to provide login/forgot password screens.
 */
class display_message
{
    protected $msg_html;

    /** @param $sess_msg_index string */
    public function __construct($sess_msg_index)
    {
        $this->msg_html = $this->display_message($sess_msg_index);
    }

    /**
     * Checks the $_SESSION variable using $sess_msg_index as the array index. If data is present, formats and returns
     * HTML for the display message. The function unsets the $_SESSION variable after the HTML is built. If no data is
     * present, returns HTML for a generic error message.
     *
     * @param $sess_msg_index string The index where the needed response message should be.
     * @return mixed The HTML response.
     */
    protected function display_message($sess_msg_index)
    {
        $session_msg = isset($_SESSION[$sess_msg_index]) ? $_SESSION[$sess_msg_index] : false;

        if ($session_msg === false)
        {
            return '';
        }

        $is_json = $this->is_json($session_msg);

        $generic_fail = '<div class="error">Your request couldn\'t be completed. Please try again later.</div>';

        if ($is_json === false)
        {
            error_log('display_message: Not in JSON format -  ' . $session_msg);
            return $generic_fail;
        }

        $json_decode = json_decode($_SESSION[$sess_msg_index], true);

        $msg_type = (int)$json_decode[0];
        $msg = (string)$json_decode[1];

        switch ($msg_type)
        {
            case 0:
                $msg_display = '<div class="error">' . $msg . '</div>';
                break;
            case 1:
                $msg_display = '<div class="good">' . $msg . '</div>';
                break;
            default:
                $msg_display = $generic_fail;
                break;
        }

        unset($_SESSION[$sess_msg_index]);
        return $msg_display;
    }

    protected function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function return_msg_html()
    {
        return $this->msg_html;
    }
}
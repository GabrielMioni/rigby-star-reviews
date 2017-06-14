<?php

class display_forgot_password_form
{
    protected $reset_id;
    protected $html;

    public function __construct()
    {
        $this->reset_id = isset($_GET['reset_id']) ? htmlspecialchars($_GET['reset_id']) : false;

        $this->html = $this->set_form_display($this->reset_id);
    }

    protected function set_form_display($reset_id)
    {
        if ($reset_id == false)
        {
            $html = $this->set_send_link_form();
        } else {
            $html = $this->set_reset_pswd_form($reset_id);
        }

        return $html;
    }

    protected function set_send_link_form()
    {
        $entered_email = $this->set_entered_email();

        $html = "<form id='forgot_password_form' name='forgot' action='password_reset_send_act.php' method='post'>
                    <div class='form_row'>
                        <label for='user'>
                            Email<br>
                        </label>
                        <input id='user' type='text' name='email' value='$entered_email'>
                    </div>
                    <div class='form_row'>
                        <input type='submit' name='submit' value='Send Password Reset'>
                    </div>
                </form>";

        return $html;
    }

    protected function set_entered_email()
    {
        return isset($_SESSION['entered_email']) ? htmlspecialchars($_SESSION['entered_email']) : '';
    }

    protected function set_reset_pswd_form($reset_id)
    {
        $reset_id = htmlspecialchars($reset_id);

        $html = "<form id='reset_password_form' name='reset_pass' action='password_reset_act.php' method='post'>
                    <div class='form_row'>
                        <label for='user'>
                            Password:<br>
                        </label>
                        <input id='password' type='password' name='password'>
                    </div>
                    <div class='form_row'>
                        <label for='confirm'>
                            Confirm:<br>
                        </label>
                        <input id='confirm' type='password' name='confirm'>
                    </div>
                    <input type='hidden' name='reset_id' value='$reset_id'>
                    <div class='form_row'>
                        <input type='submit' name='submit' value='Reset Password'>
                    </div>
                </form>";

        return $html;
    }

    public function return_html_form()
    {
        return $this->html;
    }
}
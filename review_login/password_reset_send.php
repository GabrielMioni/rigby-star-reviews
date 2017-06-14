<?php
session_start();

if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once('password_reset_abstract.php');
require_once(RIGBY_ROOT . '/php/set_rigby_home_url.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
require_once(RIGBY_ROOT . '/review_admin/php/class-phpmailer.php');

class password_reset_send extends password_reset_abstract
{
    protected $entered_email;
    public function __construct()
    {
        $this->entered_email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';

        $this->process_request();
    }

    protected function process_request()
    {
        $generic_success = 'Check your email. If the account exists, a password reset link has been sent';
        $generic_fail    = 'Your request could not be completed. Please try again later';

        /* **************************************************************
         * - Check to see if an email was submitted and in valid format.
         * **************************************************************/
        $email = $this->check_email();

        switch($email)
        {
            case 'no_post_email':
                $this->set_message(false, 'Enter the email address for your account.');
                $this->set_entered_email($this->entered_email);
                $this->send_to_forgot_password();
                break;
            case 'invalid_email':
                $this->set_message(false, 'The email you\'ve enetered is not in valid format');
                $this->set_entered_email($this->entered_email);
                $this->send_to_forgot_password();
                break;
            case 'pdo_failed_email':
                $this->set_message(false, $generic_fail);
                $this->send_to_forgot_password();
                break;
            case 'no_db_email':
                $this->set_message(true, $generic_success);
                $this->send_to_forgot_password();
                break;
            default:
                break;
        }

        /* ***************************************
         * - Build a reset ID.
         * ***************************************/
        $reset_id = $this->set_unique_reset_id();

        switch ($reset_id)
        {
            case 'pdo_failed_reset_id':
            case false:
            case null:
                $this->set_message(false, $generic_fail);
                $this->send_to_forgot_password();
                break;
            default:
                break;
        }

        /* ***************************************
         * - Update users.sql.
         * - Determine if an email should be sent.
         * ***************************************/
        $update_user = $this->update_users_reset_id($reset_id, $email);

        $send_mail_flag = null;

        switch ($update_user)
        {
            case false: // PDO was successful, but no rows were affected.
                $this->set_message(true, $generic_success);
                break;
            case true:  // PDO was successful, rows were affected.
                $this->set_message(true, $generic_success);
                $send_mail_flag = true;
                break;
            case 'reset_id_pdo_failed': // PDO failed
                $this->set_message(false, $generic_fail);
                break;
            default:    // Something funky.
                $this->set_message(false, $generic_fail);
                break;
        }

        if ($send_mail_flag == true)
        {
            $this->send_email($reset_id, $email);
        }

        $this->send_to_forgot_password();
    }

    /**
     * Evaluates if $_POST['email'] is present, if it's in valid format and if it exists in users.sql.
     *
     * @return string True if validation is passed. Else, returns a flag string representing what was wrong.
     */
    protected function check_email()
    {
        $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : false;

        if ($email == false)
        {
            return 'no_post_email';
        }

        $check = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($check == false)
        {
            return 'invalid_email';
        }

        try {
            $query = "SELECT * FROM users WHERE email = ?";

            $result = sql_pdo::run($query, [$email])->fetchAll();

            if (empty($result))
            {
                return 'no_db_email';
            }
        } catch (PDOException $e) {

            error_log($e->getMessage());
            return 'pdo_failed_email';
        }

        return $email;
    }

    /**
     * Tries to update users.sql with with a unique reset ID.
     *
     * @return bool|null|string
     */
    protected function set_unique_reset_id()
    {
        $id_is_unique = null;
        $attempts     = 0;

        $reset_id     = null;

        while ($id_is_unique == null && $attempts < 20) {
            $reset_id = $this->generate_reset_id();
            ++$attempts;

            try {
                $query = "SELECT reset_id FROM users WHERE reset_id = ?";
                $result = sql_pdo::run($query, [$reset_id])->fetchAll();

                if (empty($result)) {
                    $id_is_unique = true;
                }
            } catch (PDOException $e) {
                $id_is_unique = 'pdo_failed_reset_id';
                error_log($e->getMessage());
            }
        }

        if ($reset_id !== null)
        {
            return $reset_id;
        } else {
            error_log('Could not generate a Reset ID in ' . $attempts . 'tries');
            return false;
        }
    }

    /**
     * Generates a random reset id between 6-10 characters long consisting of digits and uppercase/lowercase alphabets.
     *
     * @return string The random reset ID been created.
     */
    protected function generate_reset_id()
    {
        $length = rand(6, 10);
        $alph   = range('a', 'x');
        $alph_count = count($alph);
        $num    = range(0, 9);

        $str = '';

        while (strlen($str) < $length)
        {
            $chooser = rand(0, 3);

            if ($chooser < 3)
            {
                $rand_alph_index = rand(0, $alph_count-1);
                $rand_alphabet = $alph[$rand_alph_index];
                $uc_or_not = rand(0,1);

                $str .= ($uc_or_not == 0) ? ucfirst($rand_alphabet) : $rand_alphabet;

            } else {
                $rand_num_index = rand(0, 9);
                $str .= $num[$rand_num_index];
            }
        }
        return $str;
    }

    /**
     * Sets the Rigby user's password reset id to the value of $reset_id.
     *
     * @param $reset_id string The reset ID that's been created.
     * @param $email string The email associated with the Rigby user's account.
     * @return bool|string True if rows were affected. False if none were. If PDO failed, returns string.
     */
    protected function update_users_reset_id($reset_id, $email)
    {
        try {
            $reset_timestamp = date('Y-m-d H:i:s', time() + 3600);

            $pdo = array();
            $pdo[] = $reset_id;
            $pdo[] = $reset_timestamp;
            $pdo[] = $email;

            $query = "UPDATE users SET reset_id = ?, reset_exp = ? WHERE email = ? LIMIT 1";

            $stmt = sql_pdo::run($query, $pdo);
            $affected_row = $stmt->rowCount();

            if ($affected_row > 0)
            {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {

            error_log($e->getMessage());
            return 'reset_id_pdo_failed';
        }
    }

    /**
     * Creates a PHPMailer object and sends an email.
     *
     * The content of the email includes a link to the $reset_id value set as part of the query string for the password
     * reset link.
     *
     * @param $reset_id string The Reset ID that's been created for the user's password reset request.
     * @param $email string The Rigby user's email address associated with their Rigby account.
     * @return bool False, PHPMailer failed. True, message was sent.
     */
    protected function send_email($reset_id, $email)
    {
        $url = set_rigby_home_url();
        $url .= '/review_login/forgot_password.php?' . "reset_id=$reset_id";

        $content  = "Click on the following link to reset your Rigby login password:\n";
        $content .= $url;

        $mail = new PHPMailer;
        $mail->setFrom('terrence@ribgy.com', 'Rigby Password Reset');
        $mail->addAddress($email, 'Rigby User');
        $mail->Subject  = 'Rigby Password Reset';
        $mail->Body     = $content;

        if(!$mail->Send())
        {
            error_log("PHPMailer: " . $mail->ErrorInfo);
            return false;
        }
        else
        {
            return true;
        }
    }

    protected function set_entered_email($entered_email)
    {
        if (trim($entered_email) !== '')
        {
            $_SESSION['entered_email'] = $entered_email;
        }
    }
}

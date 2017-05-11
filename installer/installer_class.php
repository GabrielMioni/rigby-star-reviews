<?php
require_once('../php/sql_pdo/sql_pdo.php');
require_once('../config.php');

/**
 *
 */

/**
 * The installer class walks a new Rigby user through installation.
 *
 * - Makes sure PHP version > 5
 * - Makes sure sessions are enabled
 * - Makes sure Rigby can use fopen/fwrite in its own directories.
 * - Accepts MySQL credentials. If validated, writes credentials to a define file for use by the sql_pdo class at
 *   php/sql_pdo/sql_pdo.php. Rigby user will be locked out if 3 failed attempts are made in 10 minutes.
 * - Creates users and star_reviews MySql tables.
 * - Prompts the Rigby user to create a Rigby admin account.
 *
 * Tread carefully dear reader for I have a tale.
 *
 * The Rigby user is provided with different screens using a switch calling $_POST['set_action']. The switch returns HTML
 * based on the value of $_POST['set_action']. Functions called during the switch statement are also processing things
 * like validation for user input and returning errors.
 *
 * In some cases a user can't proceed to the next step until their input passes validation. In these cases, they have
 * to be redirected to installer/index.php. To keep them from starting at the beginning, $_SESSION['set_action'] is set
 * with the value for the step they need. The installer::__constructor() checks for this value and if it's set it will
 * use the value of $_SESSION['set_value'] to display HTML.
 *
 * $_SESSION elements are also used to pass error message/previously entered input data. These are always destroyed
 * after processing HTML.
 *
 */
namespace Rigby;

class installer {
    protected $pdo_obj;

    protected $step = null;

    protected $html;

    public function __construct()
    {
        $this->step = isset($_POST['set_action']) ? $_POST['set_action'] : null;

        if ($this->step == null) {
            $this->step = isset($_SESSION['set_action']) ? $_SESSION['set_action'] : null;
            unset($_SESSION['set_action']);
        }

        $this->html = $this->set_html($this->step);
    }

    /**
     * @param $step
     * @return string
     */
    protected function set_html($step)
    {
        $server = $_SERVER['PHP_SELF'];
        $html = '';

        switch ($step)
        {
            case null:
                $html = $this->html_starting($server);
                break;
            case 1:
                $html = $this->html_requirements($server);
                break;
            case 2:
                $html = $this->html_check_php_and_session($server);
                break;
            case 3:
                $html = $this->html_collect_sql_creds($server);
                break;
            case 4:
                $html = $this->html_validate_sql_creds($server);
                break;
            case 5:
                $html = $this->html_create_rigby_admin($server);
                break;
            case 6:
                $html = $this->html_check_rigby_success($server);
                break;
            case 7:
                header('Location: ../review_login/');
                break;
        }

        return $html;
    }

    /**
     * Step null
     *
     * Displays the Rigby Installer introduction.
     *
     * @param $server string form action directory (PHP_SELF)
     * @return string HTML for the introduction screen.
     */
    protected function html_starting($server)
    {
        $html = "<div class='rigby_message good'>
                    <h3>Welcome to Rigby!</h3>
                    <p>
                        This installer will walk you through making sure everything is setup and ready to start.
                    </p>
                    <p>
                        Clink 'Start' to get stared!
                    </p>
                    <form action='$server' method='post'>
                        <button name='set_action' value='1' type='submit'>Start!</button>
                    </form>
                </div>";
        session_destroy();
        return $html;
    }

    /**
     * Step 1
     *
     * Displays HTML for the requirements screen.
     *
     * @param $server string form action directory (PHP_SELF)
     * @return string HTML for the requirements screen
     */
    protected function html_requirements($server)
    {
        $html = "    <div class='rigby_message good'>
                        <p class='top_p'>Rigby has a few requirements.<p>
                            <ul>
                                <li>PHP must be version 5 or higher</li>
                                <li>PHP must have sessions enabled</li>
                                <li>You must have a MySQL database with a username and password. Your MySQL user must have write access.</li>
                          </ul>
                          <p>The next screen will confirm all those things are ready.</p>
                        <form action='$server' method='post'>
                            <button name='set_action' value='2' type='submit'>Continue</button>
                        </form>
                      </div>";
        return $html;
    }

    /**
     * Step 2
     *
     * Checks PHP version and if sessions are enabled. Displays HTML letting the Rigby user know whether they
     * passed requirements or not.
     *
     * @param $server string form action directory (PHP_SELF)
     * @return string HTML requirements valiation screen.
     */
    protected function html_check_php_and_session($server)
    {
        $php_version_ok = $this->check_php_version();
        $sessions_ok    = $this->check_sessions_enabled();
        $write_json_ts  = $this->check_write_json_ts();

        $php_symbol  = $this->set_true_false_symbols($php_version_ok);
        $sess_symbol = $this->set_true_false_symbols($sessions_ok);
        $write_symbol = $this->set_true_false_symbols($write_json_ts);

        if ($php_version_ok === true && $sessions_ok === true && $write_json_ts === true)
        {
            $check_state = 1;
            $html = '<div class=\'rigby_message good\'>';
        } else {
            $check_state = 0;
            $html = '<div class=\'rigby_message bad\'>';
        }

        switch ($check_state) {
            case 1:
                $html .= '<p class="top_p">Looks like everything is good!</p>';
                break;
            case 2:
            default:
                $html .= '<p>There appears to be a problem.</p>';
                break;
        }

        $html .= "<table id='installer_check'>
                        <caption>Requirements</caption>
                            <tr>
                                <th>PHP version 5 or greater</th>
                                $php_symbol
                             </tr>
                             <tr>
                                <th>PHP Sessions are enabled</th>
                                $sess_symbol
                             </tr>
                             <tr>
                                <th>Rigby Can Write a file</th>
                                $write_symbol
                             </tr>
                    </table>";

        if ($check_state === 1)
        {
            $html .= "<form action='$server' method='post'>
                           <button name='set_action' value='3' type='submit'>Next</button>
                      </form>";
        } else {
            $html .= '<ul>';
            if ($php_version_ok === false)
            {
                $html .= '<li>It looks like your PHP needs to be updated.</li>';
            }
            if ($sessions_ok === false)
            {
                $html .= '<li>Rigby needs sessions to be enabled. It\'s a little weird that they aren\'t since they\'re usually enabled by default.</li>';
            }
            if ($write_json_ts === false)
            {
                $html .= '<li>Rigby doesn\'t appear to have write access to its own directory.</li>';
            }
            $html .= 'Check with your web host. They can probably help you.';
            $html .= '</ul>';
        }

        $html .= '</div>'; // close .rigby_message element.

        // Destroy the session set while testing whether sessions are enabled.
        $this->unset_session_message('test_msg');

        return $html;
    }

    /**
     * Returns a check mark if $results == true and a 'x' if $results == false.
     *
     * @param $results bool
     * @return string Font-Awesome HTML.
     */
    protected function set_true_false_symbols($results)
    {
        $checkmark_html = "<td class='true_td'><i class='fa fa-check' aria-hidden='true'></i></td>";
        $x_html         = "<td class='false_td'><i class='fa fa-times' aria-hidden='true'></i></td>";

        if ($results) {
            return $checkmark_html;
        } else {
            return $x_html;
        }
    }

    /**
     * Step 3
     *
     * @param $server
     * @return string
     */
    protected function html_collect_sql_creds($server)
    {
        $html = '';
        $db_value = $this->set_value_from_session('3_sql_db');
        $un_value = $this->set_value_from_session('3_sql_un');
        $pw_value = $this->set_value_from_session('3_sql_pw');

        if (isset($_SESSION['3_sql_error'])) {
            $error = $_SESSION['3_sql_error'];
            $html .= '<div class=\'rigby_message bad\'>';
        } else {
            $error = '';
            $html .= '<div class=\'rigby_message good\'>';
        }

        $html .= "  <p class='top_p'>Rigby requires access to a MySQL server.</p>
                    <div class='error'><p>$error</p></div>
                        <form action='$server' method='post'>
                            <div class='inputs'>
                                <div class='form_row'>
                                    <label for='sql_db'>Database: </label>
                                    <input name='sql_db' type='text' value='$db_value'>
                                </div>
                                <div class='form_row'>
                                    <label for='sql_un'>Username: </label>
                                    <input name='sql_un' type='text' value='$un_value'>
                                </div>
                                <div class='form_row'>
                                    <label for='sql_pw'>Password: </label>
                                    <input name='sql_pw' type='password' value='$pw_value'>
                                </div>
                            </div>
                            <button name='set_action' value='4' type='submit'>Next</button>
                        </form>
                    </div>";

        $this->unset_session_message('3_sql_error');
        $this->unset_session_message('3_sql_db');
        $this->unset_session_message('3_sql_un');
        $this->unset_session_message('3_sql_pw');

        return $html;
    }


    /**
     * Step 4
     *
     * @param $server
     * @return string
     */
    protected function html_validate_sql_creds($server)
    {
        $database = htmlspecialchars(trim($_POST['sql_db']));
        $username = htmlspecialchars(trim($_POST['sql_un']));
        $password = htmlspecialchars(trim($_POST['sql_pw']));

        // Check how many times the user has tried to log in unsuccessfully within the last ten minutes.
        $check_login_attempts = $this->check_login_attempts(3, 60);

        if ($check_login_attempts == false) {
            $_SESSION['set_action'] = 3;
            $_SESSION['3_sql_error'] = 'You have been locked out. Please try again in ten minutes.';
            $_SESSION['3_sql_db'] = $database;
            $_SESSION['3_sql_un'] = $username;
            $_SESSION['3_sql_pw'] = $password;
            header('Location: ' . $server);
            exit;
        }

        $pdo_works = $this->check_pdo_connect($database, $username, $password);

        $write_state = 0;

        if ($pdo_works == true)
        {
            $write_define = $this->try_to_write_sql_define($database, $username, $password);

            $write_secret_key = $this->try_to_write_secret_key();

            if ($write_define === true && $write_secret_key === true) {
                $write_state = 1;
            }
        }

        if ($pdo_works == false || $write_state == 0)
        {
            if ($write_state == 0)
            {
                $_SESSION['3_sql_error'] = "Rigby connected with MySQL using the credentials provided, but it could not save those credentials because the directory isn't writable.";
            }
            if ($pdo_works == false)
            {
                $_SESSION['3_sql_error'] = "Rigby could not connect with MySQL. Please make sure the credentials you've provided are correct. $check_login_attempts";
            }
            $_SESSION['set_action'] = 3;
            $_SESSION['3_sql_db'] = $database;
            $_SESSION['3_sql_un'] = $username;
            $_SESSION['3_sql_pw'] = $password;
            header('Location: ' . $server);
            exit;
        } else {
            require_once('test_define.php');

            $sql_table_review = $this->create_review_table();
            $sql_table_users = $this->create_users_table();

            if ($sql_table_review !== true || $sql_table_users !== true) {
                $_SESSION['3_sql_error'] = "Rigby connected with MySQL, but couldn't create the necessary tables on the MySQL database. Maybe your user privileges don't include write access.";
                $_SESSION['3_sql_db'] = $database;
                $_SESSION['3_sql_un'] = $username;
                $_SESSION['3_sql_pw'] = $password;
                $_SESSION['set_action'] = 3;
                header('Location: ' . $server);
                exit;
            }
        }

        $html = "  <div class='rigby_message good'>
                        <p>Rigby connected with MySQL and created the tables it needs!</p>
                        <p>Next you will need to create your first administrator account so you can log into Rigby. Click Next to continue.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='5' type='submit'>Next</button>
                        </form>
                    </div>";

        return $html;
    }

    /**
     * Here we're just testing to see if it's possible to write to json_time_stamp.
     *
     * @return bool
     */
    protected function check_write_json_ts()
    {
        $json_txt = "";

        return $this->try_to_write('timestamp.txt', $json_txt);
    }

    protected function check_login_attempts($attempts_max, $seconds_to_wait)
    {
        // Read file as string

        $stream = fopen("timestamp.php","r");
        $timestamp_content = stream_get_contents($stream);
        fclose($stream);

        if ($timestamp_content == '')
        {
            $timestamp_array = array();
            $timestamp_array['timestamp'] = date('Y-m-d H:i:s', time() + $seconds_to_wait);
            $timestamp_array['attempts'] = 1;

            $json_encoded = json_encode($timestamp_array);

            $this->try_to_write('timestamp.php', $json_encoded);
        } else {
            // Convert the json data to an array
            $decoded = json_decode($timestamp_content, true);

            // Get values for Timestamp and Attempts
            $db_timestamp = $decoded['timestamp'];
            $db_attempts  = $decoded['attempts'];

            // Set values for evaluation
            $unix_ts      = strtotime($db_timestamp);
            $new_attempts = $db_attempts +1;
            $current_time = time();

            // Evaluate
            $current_greater_than_attempt = $current_time > $unix_ts;
            $attempts_greater_than_max = $new_attempts > $attempts_max;

            // Process writing state and response.
            $write_state = null;

            if ($attempts_greater_than_max == true)
            {
                if ($current_greater_than_attempt == false)
                {
                    // False
                    $write_state = 1;
                    $response = false;
                } else {
                    // True
                    $write_state = 0;
                    $response = 1;
                }
            } else {
                // True
                $write_state = 1;
                $response = 1;
            }

            $timestamp_array = array();
            $timestamp_array['timestamp'] = date('Y-m-d H:i:s', time() + $seconds_to_wait);

            switch ($write_state) {
                case 0:
                    $timestamp_array['attempts']  = 1;
                    break;
                case 1:
                    $timestamp_array['attempts']  = $new_attempts;
                    break;
            }

            $write_text = json_encode($timestamp_array);
            $this->try_to_write('timestamp.php', $write_text);

            switch ($response) {
                case false:
                    return false;
                default:
                    if ($new_attempts > $attempts_max) {
                        $new_attempts = 1;
                    }
                    $attempts_left_msg = "Attempts [$new_attempts/$attempts_max] left.";
                    return $attempts_left_msg;
            }
        }
    }




    protected function try_to_write_sql_define($database, $username, $password)
    {
        $define_text = "<?php
        define('DB_HOST', 'localhost');
        define('DB_NAME', '$database');
        define('DB_USER', '$username');
        define('DB_PASS', '$password');
        define('DB_CHAR', 'utf8');        
        ?>";

        return $this->try_to_write('test_define.php', $define_text);
    }

    protected function try_to_write_secret_key()
    {
        $random_key = bin2hex(password_hash(32, MCRYPT_DEV_URANDOM));

        $secret_key_text = "<?php
        define(\"SECRET_KEY\", \"$random_key\");";

        return $this->try_to_write('../review_login/define_test.php', $secret_key_text);
    }

    protected function try_to_write($file, $text, $allow_write = false)
    {
        if (is_writable($file))
        {
            $fp = fopen($file, 'wb');
            fwrite($fp, $text);
            fclose($fp);
            if ($allow_write == false) {
                chmod($file, 0666);
            }
            return true;
        } else {
            return false;
        }
    }

    protected function create_review_table()
    {
        $query = 'CREATE TABLE reviews_test (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(60) NOT NULL,
                    name VARCHAR(30) NOT NULL,
                    email VARCHAR(50) NOT NULL,
                    cont VARCHAR(1000) NOT NULL,
                    ip VARCHAR(32) NOT NULL,
                    hidden int(1) DEFAULT NULL,
                    date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    stars tinyint(1),
                    reply VARCHAR(1000) DEFAULT NULL)';

        return $this->create_table($query);
    }

    protected function create_users_table()
    {
        $query = 'CREATE TABLE users_test (
                    id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(30) NOT NULL,
                    email VARCHAR(50) NOT NULL,
                    hash char(64) DEFAULT NULL,
                    reg_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    token char(64) DEFAULT NULL,
                    token_exp datetime DEFAULT NULL,
                    admin tinyint(1) DEFAULT NULL,
                    locked tinyint(1) NOT NULL DEFAULT 0)';

        return $this->create_table($query);
    }

    protected function create_table($query) {
        try {
            sql_pdo::run($query);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }


    /**
     * Step 5
     *
     * @param $server
     * @return string
     */
    protected function html_create_rigby_admin($server)
    {
        $pseudo_ajax_reply = $this->set_value_from_session('pseudo_ajax_reply');

        $error_name  = $this->set_value_from_session('6_user_error_name');
        $error_email = $this->set_value_from_session('6_user_error_email');
        $error_pass  = $this->set_value_from_session('6_user_error_password');
        $error_conf  = $this->set_value_from_session('6_user_error_confirm');

        $user_val  = $this->set_value_from_session('6_val_user');
        $email_val = $this->set_value_from_session('6_val_email');
        $pass_val  = $this->set_value_from_session('6_val_pass');
        $conf_val  = $this->set_value_from_session('6_val_conf');


        if ($pseudo_ajax_reply !== '')
        {
            $error_array = array();
            $error_array[] = $error_name;
            $error_array[] = $error_email;
            $error_array[] = $error_pass;
            $error_array[] = $error_conf;

            $error_count = 0;

            $error_display = '<ul class=\'error\'>';
            foreach ($error_array as $error_li)
            {
                if ($error_li !== '')
                {
                    $error_display .= "<li>$error_li</li>";
                    ++$error_count;
                }
            }
            $error_display .= '</ul>';

            $html  = "<div class='rigby_message bad '>";
            if ($error_count > 1) {
                $html .= '<p>It looks like there were some problems:</p>';
            } else {
                $html .= '<p>Rigby needs you to make a corrections.</li>';
            }
            $html .= $error_display;

        } else {
            $html = "<div class='rigby_message good '>";
        }

        $html .= "      <p>Below enter your username, email address and enter a password. You will use these credentials to log into Rigby. All fields must be entered.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <div class='inputs'>
                                <div class='form_row'>
                                    <label for='new_name'>Username: </label>
                                    <input name='new_name' type='text' value='$user_val'>
                                </div>
                                <div class='form_row'>
                                    <label for='new_email'>Email: </label>
                                    <input name='new_email' type='text' value='$email_val'>
                                </div>
                                <div class='form_row'>
                                    <label for='pswd_set'>Password: </label>
                                    <input name='pswd_set' type='password' value='$pass_val'>
                                </div>
                                <div class='form_row'>
                                    <label for='pswd_con'>Confirm: </label>
                                    <input name='pswd_con' type='password' value='$conf_val'>
                                </div>
                                <input type='hidden' name='priv' value='1'>
                            </div>
                            <button name='set_action' value='6' type='submit'>Next</button>
                        </form>
                    </div>";

        $this->unset_session_message('pseudo_ajax_reply');
        $this->unset_session_message('6_user_error_name');
        $this->unset_session_message('6_user_error_email');
        $this->unset_session_message('6_user_error_password');
        $this->unset_session_message('6_user_error_confirm');

        $this->unset_session_message('6_val_user');
        $this->unset_session_message('6_val_email');
        $this->unset_session_message('6_val_pass');
        $this->unset_session_message('6_val_conf');

        return $html;
    }

    /**
     * Step 6
     *
     * @param $server
     * @return string
     */
    protected function html_check_rigby_success($server)
    {
        require_once('../review_admin/php/add_user.php');

        $add_user = new add_user(true);

        // There's no actual Ajax here, but we'll use the json_encoded response for feedback.
        $pseudo_ajax_reply = $add_user->get_ajax_reply();

        if ($pseudo_ajax_reply !== 1)
        {
            $error_array = json_decode($pseudo_ajax_reply, true);

            foreach ($error_array as $key => $error_msg)
            {
                $session_name = '6_user_error_' . $key;
                $_SESSION[$session_name] = $error_msg;
            }

            $_SESSION['6_val_user']  = htmlspecialchars(trim($_POST['new_name']));
            $_SESSION['6_val_email'] = htmlspecialchars(trim($_POST['new_email']));
            $_SESSION['6_val_pass']  = htmlspecialchars(trim($_POST['pswd_set']));
            $_SESSION['6_val_conf']  = htmlspecialchars(trim($_POST['pswd_con']));

            $_SESSION['set_action'] = 5;
            $_SESSION['pseudo_ajax_reply'] = 1;
            header('Location: ' . $server);
            exit;
        }

        $html = "<div class='rigby_message good'>
                        <h3>Congratulations</h3>
                        <p>You've finished the installation process and created a Rigby admin login.</p>
                        <p>Click the button below to go to the admin login page.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='7' type='submit'>Go to login!</button>
                        </form>
                    </div>";

        return $html;

    }

    protected function check_redirect()
    {
        if (isset($_SESSION['set_action'])) {
            $_POST['set_action'] = $_SESSION['set_action'];
            unset($_SESSION['set_action']);
        }
    }

    protected function set_value_from_session($session_name)
    {
        if (isset($_SESSION[$session_name]))
        {
            return htmlspecialchars($_SESSION[$session_name]);
        } else {
            return '';
        }
    }

    protected function unset_session_message($session_name)
    {
        if (isset($_SESSION[$session_name])) {
            unset($_SESSION[$session_name]);
        }
    }

    /* ***************************************
     * Pre-Installation
     * - PHP must be at least 5
     * - Sessions must be enabled.
     * ***************************************/

    /**
     * Checks if the version of PHP on the server is at least 5.
     *
     * @return bool True if the PHP version is >= 5, else False.
     */
    protected function check_php_version()
    {
        $required = 5;
        $php_version = phpversion();
        return ($php_version >= $required);
    }

    /**
     * Checks if sessions are enabled on the PHP server. Does so by trying to create a value for $_SESSION['test_msg]
     *
     * @return bool True if $_SESSION could be set. Else false.
     */
    protected function check_sessions_enabled()
    {
        $_SESSION['test_msg'] = 1;
        if (isset($_SESSION['test_msg']))
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rigby needs a valid MySQL database, username and password. This tests
     * whether the credentials provided will connect.
     *
     * Sets installer_class::$pdo_obj with a PDO object using the provided creds if they're valid.
     *
     * @param $db_name string The name of the MySQL database.
     * @param $db_user string The name of the SQL user.
     * @param $db_pass string The password for $db_user
     * @return bool Returns True if creds are valid, else returns False.
     */
    protected function check_pdo_connect($db_name, $db_user, $db_pass)
    {
        $opt  = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => FALSE,
        );

        $dsn = "mysql:host=localhost;dbname=$db_name;charset=utf8";

        try {
            new PDO($dsn, $db_user, $db_pass, $opt);
        } catch (PDOException $e) {
            return false;
        }
        // Return true if no exception is caught.
        return true;
    }

    /**
     * Checks to make sure $pdo_obj is an instance of PDO. If not, return false. Else,
     * check if the Rigby star_review table exists by calling installer_class::query_rigby_table().
     *
     * @param $pdo_obj PDO|null If check_pdo_connect() successfully created a PDO object,
     * @uses installer::query_rigby_table()
     * @return bool If Rigby star_review table exists, returns True. Else if either $pdo_obj isn't
     * an instance of PDO or the table doesn't exist, return false.
     */
    protected function check_rigby_table_exists($pdo_obj)
    {
        if($pdo_obj instanceof PDO) {

            $check_for_table = $this->query_rigby_table($pdo_obj);
            return $check_for_table;
        } else {
            return false;
        }
    }

    /**
     * Checks to see if Rigby star_review table exists.
     *
     * @param PDO $pdo_obj Created by installer::check_pdo_connect()
     * @return bool If table exists return true. Else return false.
     */
    protected function query_rigby_table(PDO $pdo_obj)
    {
        $out = false;

        try {
            $query = "SELECT 1 FROM star_reviews LIMIT 1";
            $run_check = $pdo_obj->query($query);
            $results = $run_check->fetchColumn();
            if ($results)
            {
                $out = true;
            }

        } catch(PDOException $e) {
            $out = false;
        }
        return $out;

    }

    /**
     * This is just for Gabriel and he'll delete this when everything is nice.
     *
     * @param $bool
     * @return string
     */
    protected function show_bool($bool)
    {
        switch ($bool)
        {
            case true:
                return "True";
            case false:
                return "False";
            default:
                return "False";
        }
    }

    public function return_html()
    {
        return $this->html;
    }
}

<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * The installer_class is responsible for the following:
 *
 * - Check the requirements for Rigby.
 * - Test the MySQL connection using creds provided by the user.
 * - Save MySQL creds in /php/sql_pdo/sql_define.php if they're good.
 * - Build the MySQL tables necessary for Rigby to run.
 * - Prompt the Rigby user to create their first Rigby admin account.
 * - Direct the user to the Rigby login page.
 */
class installer_class
{
    /** @var int Flag used by installer_class::set_html() that directs the class to set HTML for each installation step */
    protected $step = 0;

    /** @var string The HTML for the installation step that needs to be displayed. */
    protected $html;

    public function __construct()
    {
        $this->step = $this->set_step();

        $this->html = $this->set_html($this->step);
    }

    /**
     * Sets the $step flag that directs the installer to load the necessary HTML.
     *
     * If $_SESSION['set_action'] is set it will over rule $_POST['set_action']. This lets the step be set manually
     * by installer_class::set_step_manually() when there's some issue with form validation or a given step failed.
     *
     * @return int The flag representing what step the installer should be on.
     */
    protected function set_step()
    {
        if (isset($_SESSION['set_action']))
        {
            $out = $_SESSION['set_action'];
            $this->unset_value_from_session('set_action');
            return $out;
        }
        if (isset($_POST['set_action']))
        {
            $out = $_POST['set_action'];
            return $out;
        }
    }

    /**
     * Evaluates $step and sets which method should be called to display HTML.
     *
     * @param $step int Set by installer_class:set_step().
     * @return string HTML that should be displayed.
     */
    protected function set_html($step)
    {
        $server = htmlspecialchars($_SERVER['PHP_SELF']);

        switch ($step)
        {
            case 0: // Start screen
                $html = $this->start_screen($server);
                break;
            case 1: // Explain requirements.
                $html = $this->explain_requirements($server);
                break;
            case 2: // Check requirements.
                $html = $this->check_requirements($server);
                break;
            case 3: // Confirm requirements passed. Request SQL creds.
                $html = $this->request_sql_creds($server);
                break;
            case 4: // Test PDO - Write sql_define.php
                $html = $this->write_sql_define($server);
                break;
            case 5: // Create Rigby MySQL tables.
                $html = $this->create_rigby_tables($server);
                break;
            case 6: // Create Rigby Admin account.
                $html = $this->create_rigby_admin($server);
                break;
            case 7: // Congratulate Rigby user.
                $html = $this->all_done($server);
                break;
            case 8:
                $html = '';
                $this->go_to_login();
                break;
            default:
                $html = '';
                $this->go_to_login();
                break;
        }

        /* Always clean $_SESSION['error'] so it's clean for the next step in the installer. */
        $this->unset_value_from_session('error');

        return $html;
    }

    /**
     * Step 0. Show the Rigby user the installer intro.
     *
     * @param $server string $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function start_screen($server)
    {
        $html = "<div class='rigby_message good'>
                    <h3>Welcome to Rigby!</h3>
                    <p>This installer will walk you through making sure everything is set up and ready to start.</p>
                    <p>Clink 'Start' to get stared!</p>
                    <form action='$server' method='post'>
                        <button name='set_action' value='1' type='submit'>Start!</button>
                    </form>
                </div>";

        return $html;
    }

    /**
     * Step 1. Displays requirements.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function explain_requirements($server)
    {
        $html = "<div class='rigby_message good'>
                    <p class='top_p'>Rigby has a few requirements.<p>
                        <ul><li>PHP must be version 5 or higher</li><li>PHP must have sessions enabled</li><li>You must have a MySQL database with a username and password. Your MySQL user must have write access.</li></ul>
                        <p>The next screen will confirm all those things are ready.</p>
                        <form action='$server' method='post'><button name='set_action' value='2' type='submit'>Continue</button></form>
                 </div>";

        return $html;
    }

    /**
     * Step 2. Checks requirements and provides feedback on results.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function check_requirements($server)
    {
        /* Run checks to make sure stuff that the installer needs is in place */
        $php_version_ok = $this->check_php_version();
        $sessions_ok    = $this->check_sessions_enabled();
        $write_json_ts  = $this->check_write_json_ts();

        $checkmark_html = "<td class='true_td'><i class='fa fa-check' aria-hidden='true'></i></td>";
        $x_html         = "<td class='false_td'><i class='fa fa-times' aria-hidden='true'></i></td>";

        /* Check validation and set HTML parts accordingly. */
        if ($php_version_ok === true && $sessions_ok === true && $write_json_ts === true)
        {
            /* Set check mark symbols for all table rows if validation is passed. */
            $php_symbol   = $checkmark_html;
            $sess_symbol  = $checkmark_html;
            $write_symbol = $checkmark_html;

            /* Set open div element with 'good' class */
            $div_start = '<div class=\'rigby_message good\'>';

            /* Set button to next step */
            $end_info = "<form action='$server' method='post'><button name='set_action' value='3' type='submit'>Next</button></form>";

        } else {

            /* If validation did not pass, evaluate check mark or 'x' for each row. */
            $php_symbol   = $php_version_ok === true ? $checkmark_html : $x_html;
            $sess_symbol  = $sessions_ok    === true ? $checkmark_html : $x_html;
            $write_symbol = $write_json_ts  === true ? $checkmark_html : $x_html;

            /* Set open div element with 'bad' class */
            $div_start = '<div class=\'rigby_message bad\'>';

            /* Set error feedback in a <ul> element */
            $end_info = '<ul>';

            if ($php_version_ok === false)
            {
                $end_info .= '<li>It looks like your PHP needs to be updated.</li>';
            }
            if ($sessions_ok === false)
            {
                $end_info .= '<li>Rigby needs sessions to be enabled.</li>';
            }
            if ($write_json_ts === false)
            {
                $end_info .= '<li>Rigby couldn\'t write to it\'s own directory.</li>';
            }
            $end_info .= '</ul>';
        }

        /* Put the HTML parts together. */
        $html = '';

        $html .= $div_start; // Open the div element.
        $html .= "<table id='installer_check'><caption>Requirements</caption><tr><th>PHP version 5 or greater</th>$php_symbol</tr><tr><th>PHP Sessions are enabled</th>$sess_symbol</tr><tr><th>Rigby Can Write a file</th>$write_symbol</tr></table>";
        $html .= $end_info;

        $html .= '</div>'; // close the div element.

        return $html;
    }

    /**
     * Step 3. Collect MySQL creds and test them. Creds are tested in Step 4. If validation fails, user is sent back
     * to Step 3 and an error is displayed.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function request_sql_creds($server)
    {
        /* Set any values that should be set in the form inputs if they're present. */
        $db_value = $this->set_value_from_session('sql_db');
        $un_value = $this->set_value_from_session('sql_un');
        $pw_value = $this->set_value_from_session('sql_pw');
        $error    = $this->set_value_from_session('error');

        /* Check if error is set and set the <div> open with the appropriate good/bad class. */
        if (trim($error === ''))
        {
            $html = "<div class='rigby_message good'>";
        } else {
            $html = "<div class='rigby_message bad'>";
        }

        $html .= "<p class='top_p'>Rigby requires access to a MySQL server.</p>
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

        /* Clean $_SESSION variables for Step 4. */
        $this->unset_value_from_session('sql_db');
        $this->unset_value_from_session('sql_un');
        $this->unset_value_from_session('sql_pw');
        $this->unset_value_from_session('error');

        return $html;
    }

    /**
     * Step 4. Evaluate $_POST data from Step 3, tests the MySQL creds and if the creds are good, write them to
     * /php/sql_pdo/sql_define.php.
     *
     * If any values are empty or if the MySQL connection fails, send the user back to Step 3.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function write_sql_define($server)
    {
        $database = $this->set_value_from_post('sql_db');
        $username = $this->set_value_from_post('sql_un');
        $password = $this->set_value_from_post('sql_pw');

        $step_back = 3;

        /* Make sure post values aren't empty. */
        if ($database === '' || $username === '' || $password === '')
        {
            $_SESSION['sql_db'] = $database;
            $_SESSION['sql_un'] = $username;
            $_SESSION['sql_pw'] = $password;
            $_SESSION['error']  = 'Fields cannot be empty!';

            $this->set_step_manually($step_back, $server);
        }

        /* Test the PDO connection with the post values provided. */
        $check_pdo = $this->check_pdo_connect($database, $username, $password);

        if ($check_pdo === false)
        {
            $_SESSION['sql_db'] = $database;
            $_SESSION['sql_un'] = $username;
            $_SESSION['sql_pw'] = $password;
            $_SESSION['error']  = 'Could not connect with MySQL using the credentials you\'ve supplied.';

            $this->set_step_manually($step_back, $server);
        }

        /* Try to write to /php/sql_pdo/sql_define.php */
        $define_text = "<?php
        define('DB_HOST', 'localhost');
        define('DB_NAME', '$database');
        define('DB_USER', '$username');
        define('DB_PASS', '$password');
        define('DB_CHAR', 'utf8');        
        ?>";

        $write_sql_define = $this->try_to_write('../php/sql_pdo/sql_define.php', $define_text);

        if ($write_sql_define === false)
        {
            $_SESSION['sql_db'] = $database;
            $_SESSION['sql_un'] = $username;
            $_SESSION['sql_pw'] = $password;
            $_SESSION['error']  = 'Rigby connected with MySQL but could not save your credentials.';

            $this->set_step_manually($step_back, $server);
        }

        /* If all validation / checks passed, display success HTML*/
        $html = "  <div class='rigby_message good'>
                        <p>Rigby connected with MySQL!</p>
                        <p>Next Rigby will create some MySQL tables it needs to work properly.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='5' type='submit'>Next</button>
                        </form>
                    </div>";

        return $html;
    }

    /**
     * Step 5. Try to create the MySQL tables necessary to run Rigby. If table creation fails, returns the user
     * back to Step 5 (same step) and displays an error.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function create_rigby_tables($server)
    {
        if (isset($_SESSION['error']))
        {
            $html = "<div class='rigby_message bad'>
                        <p>Rigby was unable to create the MySQL tables necessary to work properly.</p>
                        <p>Click 'Next' to try again. If the problem persists, make sure the MySQL credentials you've 
                           provided to Rigby have write access.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='5' type='submit'>Next</button>
                        </form>
                    </div>";

            $this->unset_value_from_session('error');
            return $html;
        }

        $table_reviews  = $this->table_create_reviews();
        $table_users    = $this->table_create_users();
        $table_products = $this->table_create_products();

        if ($table_reviews === false || $table_users === false || $table_products === false)
        {
            $_SESSION['error'] = true;
            $this->set_step_manually(5, $server);
            exit;
        }

        $html = "<div class='rigby_message good'>
                        <p>Rigby successfully created the tables it needs to work properly!</p>
                        <p>Next Rigby will ask you to create your first Rigby admin account.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='6' type='submit'>Next</button>
                        </form>
                    </div>";

        return $html;

    }

    /**
     * Step 6. Prompt the user to create an Admin account that's used to log into Rigby Admin. Validation is performed
     * in Step 7. If validation fails, user is sent back to Step 6 and shown errors.
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function create_rigby_admin($server)
    {
        $error    = $this->set_value_from_session('error');
        $username = $this->set_value_from_session('rigby_un');
        $email    = $this->set_value_from_session('rigby_email');
        $password = $this->set_value_from_session('rigby_pass');
        $confirm  = $this->set_value_from_session('rigby_conf');

        switch (trim($error))
        {
            case '':
                $html  = "<div class='rigby_message good '>";
                break;
            default:
                $html  = "<div class='rigby_message bad '>";
                break;
        }

        $html .= "      <p>Below enter your username, email address and enter a password. You will use these credentials to log into Rigby. All fields must be entered.</p>
                        <div class='error'>$error</div>
                        <form action='$server' method='post'>
                            <div class='inputs'>
                                <div class='form_row'>
                                    <label for='new_name'>Username: </label>
                                    <input name='new_name' type='text' value='$username'>
                                </div>
                                <div class='form_row'>
                                    <label for='new_email'>Email: </label>
                                    <input name='new_email' type='text' value='$email'>
                                </div>
                                <div class='form_row'>
                                    <label for='pswd_set'>Password: </label>
                                    <input name='pswd_set' type='password' value='$password'>
                                </div>
                                <div class='form_row'>
                                    <label for='pswd_con'>Confirm: </label>
                                    <input name='pswd_con' type='password' value='$confirm'>
                                </div>
                                <input type='hidden' name='priv' value='1'>
                            </div>
                            <button name='set_action' value='7' type='submit'>Next</button>
                        </form>
                    </div>";

        $this->unset_value_from_session('error');
        $this->unset_value_from_session('rigby_un');
        $this->unset_value_from_session('rigby_email');
        $this->unset_value_from_session('rigby_pass');
        $this->unset_value_from_session('rigby_conf');

        return $html;
    }

    /**
     * Step 7
     *
     * @param $server $_SERVER['PHP_SELF'] set in installer_class::set_html()
     * @return string HTML
     */
    protected function all_done($server)
    {
        require_once('../review_admin/php/add_user.php');

        /* The add_user class picks up the $_POST required to create a user. */
        $add_user = new add_user(true);

        /* Get result from add_user class. */
        $psuedo_ajax_reply = $add_user->get_ajax_reply();

        /* If the result is not 1, no user was created. Set errors. */
        if ($psuedo_ajax_reply !== 1)
        {
            $json_decode_error_array = json_decode($psuedo_ajax_reply, true);

            $error  = '<p>Rigby was unable to create an Admin account</p>';
            $error .= '<ul>';

            foreach ($json_decode_error_array as $value)
            {
                $error .= "<li>$value</li>";
            }
            $error .= '</ul>';

            $_SESSION['error'] = $error;

            $this->set_step_manually(6, $server);
        }

        $html = "<div class='rigby_message good'>
                        <h3>Congratulations</h3>
                        <p>You've finished the installation process and created a Rigby admin login.</p>
                        <p>Click the button below to go to the admin login page.</p>
                        <div class='error'><p></p></div>
                        <form action='$server' method='post'>
                            <button name='set_action' value='8' type='submit'>Go to login!</button>
                        </form>
                    </div>";

        return $html;
    }

    /**
     * Step 8. Take the user to the Admin page so they can login.
     * @return void
     */
    protected function go_to_login()
    {
        header('Location: ../review_login/');
    }

    /* ********************************************************
     *  - Checks used for installer_class::check_requirements()
     * ********************************************************/

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
     * Here we're just testing to see if it's possible to write to json_time_stamp.
     *
     * @return bool
     */
    protected function check_write_json_ts()
    {
        $json_txt = "";

        return $this->try_to_write('timestamp.php', $json_txt);
    }

    /* ********************************************************
     *  - Checks used for installer_class::write_sql_define()
     * ********************************************************/

    /**
     * @param $db_name string
     * @param $db_user string
     * @param $db_pass string
     * @return bool If PDO was successful, return true. Else false.
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
            return true;
        } catch (PDOException $e) {
            return false;
        }

    }

    /**
     * Starts and resets the JSON encdoed data on timestamp.php
     *
     * @param $max_attempts
     * @param $seconds_to_add
     * @return string
     */
    protected function set_new_timestamp_data($max_attempts, $seconds_to_add)
    {
        $timestamp_array = array();
        $timestamp_array['timestamp'] = date('Y-m-d H:i:s', time() + $seconds_to_add);
        $timestamp_array['attempts'] = 1;

        $json_encoded = json_encode($timestamp_array);

        $this->try_to_write('timestamp.php', $json_encoded);

        $result[] = 1;
        $result[] = "Login failed. Attempts 1 of $max_attempts.";
        return json_encode($result);
    }

    /* *******************************************************************************
     *  - MySQL Table Creation methods used in installer_class::create_rigby_tables()
     * *******************************************************************************/

    /**
     * @return bool True if table was created. Else false.
     */
    protected function table_create_reviews()
    {
        $query = 'CREATE TABLE star_reviews (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(60) NOT NULL,
                    name VARCHAR(30) NOT NULL,
                    email VARCHAR(50) NOT NULL,
                    cont VARCHAR(1000) NOT NULL,
                    ip VARCHAR(32) NOT NULL,
                    product VARCHAR (10) DEFAULT 0,
                    hidden int(1) DEFAULT NULL,
                    fake tinyint(1) DEFAULT NULL,
                    date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    stars tinyint(1),
                    reply VARCHAR(1000) DEFAULT NULL)';

        $create = $this->table_create($query);
        return $create;
    }

    /**
     * @return bool True if table was created. Else false.
     */
    protected function table_create_users()
    {
        $query = 'CREATE TABLE users (
                    id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(30) NOT NULL UNIQUE ,
                    email VARCHAR(50) NOT NULL UNIQUE ,
                    hash char(64) DEFAULT NULL,
                    reg_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    token char(64) DEFAULT NULL,
                    token_exp datetime DEFAULT NULL,
                    reset_id char(10) DEFAULT  NULL UNIQUE ,
                    reset_exp datetime DEFAULT NULL,
                    admin tinyint(1) DEFAULT NULL,
                    locked tinyint(1) NOT NULL DEFAULT 0)';

        $create = $this->table_create($query);
        return $create;
    }

    /**
     * @return bool True if table was created. Else false.
     */
    protected function table_create_products()
    {
        $query = 'CREATE TABLE products (
                    id SMALLINT (5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    product_id VARCHAR(10) NOT NULL UNIQUE ,
                    product_name VARCHAR(50) NOT NULL,
                    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    last_review DATE DEFAULT NULL)';

        $create = $this->table_create($query);
        return $create;
    }

    /**
     * @param $query string The table creation query being executed.
     * @return bool True if PDO was successful. Else false.
     */
    protected function table_create($query)
    {
        try {
            sql_pdo::run($query);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /* ********************************************************
     *  - General methods.
     * ********************************************************/

    /**
     * @param $file string The file that should be written too.
     * @param $text string The text that needs to be added.
     * @param bool $set_chmod If true, sets chmod to be un-writable after the
     * @return bool If the file was written too return true. Else, false.
     */
    protected function try_to_write($file, $text, $set_chmod = false)
    {
        if (is_writable($file))
        {
            $fp = fopen($file, 'wb');
            $fwrite = fwrite($fp, $text);
            fclose($fp);

            if ($set_chmod === true)
            {
                chmod($file, 0666);
            }

            if ($fwrite !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns value of post variables.
     *
     * @param $post_index string The post index for the post variable being looked for.
     * @return string If $_POST[$post_index] is set, return variable value. Else return ''.
     */
    protected function set_value_from_post($post_index)
    {
        if (isset($_POST[$post_index]))
        {
            return htmlspecialchars(trim($_POST[$post_index]));
        } else {
            return '';
        }
    }

    /**
     * Returns value of session variables.
     *
     * @param $session_index string The session index being requested.
     * @return string If $_SESSION[$session_index] is present, returns the value.
     */
    protected function set_value_from_session($session_index)
    {
        if (isset($_SESSION[$session_index]))
        {
            return htmlspecialchars($_SESSION[$session_index]);
        } else {
            return '';
        }
    }

    /**
     * Destroys sessions variables at $_SESSION[$session_index].
     *
     * @param $session_index string The session index for the session element that needs destroying.
     */
    protected function unset_value_from_session($session_index)
    {
        if (isset($_SESSION[$session_index]))
        {
            unset($_SESSION[$session_index]);
        }
    }

    /**
     * Used to direct the user to a specific step in the installation process. Used when there are form
     * validation issues or a given step failed.
     *
     * @param $step int The step the user is being sent to. Evaluated by installer_class::set_step()
     * @param $server string The page the user is being directed to.
     */
    protected function set_step_manually($step, $server)
    {
        $_SESSION['set_action'] = $step;
        header('Location: ' . $server);
        exit;
    }

    /**
     * @return string The HTML that's been set in installer_class::set_html().
     */
    public function return_html()
    {
        return $this->html;
    }

}
<?php
session_start();

require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');
require_once('review_insert.php');
require_once('../config.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This class validates data processed from a form action when a user submits a review.
 * 
 * $_POST data is passed through the class constructor into a single array ($post_array).
 * 
 * If any errors are found the class sets the $_SESSION array with appropriate $keys and values, which
 * are used to give the user error feedback on the review form.
 * 
 * Class is used at review_submit_act.php. I decided to break the class out from the form action for
 * better reuse.
 * 
 * While validating sumbmission, the class looks for HTML tags in the review content. If any are found
 * the review will be set to hidden.
 *
 */
class review_submit {
    protected $is_ajax;

    /** @var array Holds $_POST data from the form action inputs. */
    protected $post_array = array();

    protected $url;

    protected $ajax_result;

    protected $insert_err = null;

    /** @var mixed|null Used as a flag indicating if errors were found */
    protected $error_found = null;
    
    public function __construct($is_ajax = false) {
        $this->is_ajax = $is_ajax;
        $this->url     = isset($_POST['url']) ? $this->check_url($_POST['url']) : false;

        $this->post_array['email']      = isset($_POST['email'])   ? $this->check_email($_POST['email'])        : -1;
        $this->post_array['name']       = isset($_POST['name'])    ? $this->check_text_input($_POST['name'])    : -1;
        $this->post_array['product']    = isset($_POST['product']) ? $this->check_text_input($_POST['product']) : -1;
        $this->post_array['ip']         = $this->check_ip($_SERVER['REMOTE_ADDR']);
        $this->post_array['comment']    = isset($_POST['comment']) ? $this->check_rev_txt($_POST['comment'])    : -1;
        $this->post_array['star_rev']   = isset($_POST['star_rev'])? $this->check_star_rev($_POST['star_rev'])  : -1;
        $this->post_array['title']      = isset($_POST['title'])   ? $this->check_text_input(($_POST['title'])) : -1;
        $this->post_array['hidden']     = $this->check_hidden();

        $this->try_insert($this->post_array, $this->is_ajax, $this->url);
    }

    /**
     * Checks the $_POST['url'] value to make sure that it's a valid URL and that it matches $_SERVER data where
     * the review_submit class is called.
     *
     * @param $url string The valued passed from $_POST['url']
     * @return string|bool If $url passes validation, return $url. Else, return false.
     */
    protected function check_url($url)
    {
        $check = filter_var($url, FILTER_VALIDATE_URL);

        // Build the URL where the review_submit class is called.
        $class_url  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $class_url .= '://' . $_SERVER['HTTP_HOST'];

        // Get the length of $class_url and the a slice of $match_url from char 0 to $url_length.
        $url_length = strlen($class_url);
        $match_url  = substr($url, 0, $url_length);

        // Compare $class_url with $match_url.
        if ($class_url == $match_url)
        {
            $match_state = true;
        } else {
            $match_state = false;
        }

        if ($check == true && $match_state == true)
        {
            return $url;
        } else {
            return false;
        }
    }

    /**
     * Checks if there have been previous reviews left by Rigby users with the same email or IP address.
     * If reviews have been left in the time before specified by $wait_time, function will return false.
     * Else, returns true.
     *
     * @param $current_email string The value passed from $_POST['email'].
     * @param $current_ip string The value passed from $_SERVER['REMOTE_ADDR'].
     * @param $wait_time int The number of seconds Rigby will wait before allowing another review submission.
     * @return bool Returns true if the submit time is greater than the allowed time. Else, returns false.
     */
    protected function check_previous_reviews($current_email, $current_ip, $wait_time)
    {
        // Check if any previous reviews exist and get the date of the most recent.
        $query  = "SELECT date from star_reviews WHERE email =? OR ip =? ORDER BY date desc LIMIT 1";
        $result = sql_pdo::run($query, [$current_email, $current_ip])->fetchColumn();

        $current_time = time();
        $last_submit_time = strtotime($result) + $wait_time;

        // Check if the last review submission took place less than 60 seconds ago.
        if ($current_time > $last_submit_time)
        {
//            $okay_state = 1;
            $okay_state = true;
        } else {
//            $okay_state = -1;
            $okay_state = false;
        }

        // If no previous submissions were found, always return true.
        if ($result == '')
        {
//            $okay_state = 1;
            $okay_state = true;
        }

        return $okay_state;
    }

    /**
    * Deprecated
    *
    * @access   protected
    * @return   void
    */
    protected function set_error_found() {
        if ($this->error_found === null) {
            $this->error_found = 1;
        }
    }
    
    /**
    * Checks text inputs. If the input was whitespace, validation fails.
    *
    * @param $text string The text being validated.
    * @return   string
    */
    protected function check_text_input($text) {
        switch ($text) {
            case '':
                return -1;
            default:
                return strip_tags($text);
        }
    }
    
    /**
    * Checks review text content. If content contains HTML, return 1. This
    * will set the review row in reviews.sql to be hidden.
    *
    * @access   protected
    * @return   integer
    */
    protected function check_hidden()
    {
        if (isset($_POST['comment']))
        {
            $comment = $_POST['comment'];
        } else {
            $comment = '';
        }
        if($comment != strip_tags($comment))
        {
            // contains HTML
            return 1;
        } else {
            return 0;
        }
        
    }
    
    /**
    * Validates IP address. If validation fails, return bunk IP
    *
    * @access   protected
    * @return   string
    */
    protected function check_ip($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return '111.11.111';
        } else {
            return strip_tags($ip);
        }
    }
    
    /**
    * Processes review text. Strips tags, converts new lines to &lt;br&gt;
    *
    * @access   protected
    * @return   string
    */
    protected function check_rev_txt($rev_text) {
        switch ($rev_text) {
            case '':
                return -1;
            default:
                return nl2br(strip_tags($rev_text));
        }
    }
    
    /**
    * Processes star rating value. Confirms variable is a whole number between 1-5.
    * If not, validation fails.
    *
    * @access   protected
    * @return   integer
    */
    protected function check_star_rev($star_rev) {
        $is_whole = (!strpos($star_rev, '.'))               ? 1 : 0;
        $is_int   = (is_numeric($star_rev))                 ? 1 : 0;
        $is_vote  = (($star_rev >= 1) && ($star_rev <= 5))  ? 1 : 0;

        $code = $is_whole . $is_int . $is_vote;
        
        switch ($code) {
            case 111:
                return $star_rev;
            default:
                return -1;
        }
    }
    
    /**
    * Validates email. If validation fails, passes value to ['bad_email'] so
    * it can be converted to a $_SESSION and populated in the email input field.
    *
    * @access   protected
    * @return   mixed integer|string
    */
    protected function check_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($email !== '') {
                $this->post_array['bad_email'] = $email;                
            }
            return -1;
        } else {
            return $email;
        }
    }

    /**
     * Looks to see if any form inputs failed validation by searching the
     * $this->post_array for -1.
     *
     * If form validation failed for any form input, returns 1. Else returns null.
     *
     * @param array $post_array
     * @return bool True if no errors are found, False if errors were found.
     */
    protected function check_for_errors(array $post_array)
    {
        $error_search = array_search(-1, $post_array);
        if ($error_search)
        {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks $error_found flag to see if any form validation failed.
     * If not, try and insert the review. Redirect to 'thank you' URL message
     *
     * @param $post_array array Validation values from the review form input elements.
     * @param $is_ajax bool Flag for whether the request is from Ajax or not.
     * @param $url string Passed from $_POST['url'].
     */
    protected function try_insert(array $post_array, $is_ajax, $url)
    {
        if ($url == false )
        {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        /** @var null|int $result_flag Holds an integer represting the state of some checks done before processing the submit*/
        $result_flag = null;

        /** @var int|null $no_errors Check if errors are found in the $post_array. */
        $no_errors = $this->check_for_errors($post_array);

        // If errors no errors were found, set $result_flag to 1. Else, if errors were found set $result_flag to 2.
        if ($no_errors == true)
        {
            $result_flag = 1;
        } else {
            $result_flag = 2;
        }
        // If no errors were found, check if the Rigby user has submitted a review in the last 10 seconds.
        if ($result_flag !== 2)
        {
            $submit_date_okay = $this->check_previous_reviews($post_array['email'], $post_array['ip'], 10);
            if ($submit_date_okay == false)
            {
                // Submit time is not okay
                $result_flag = 3;
            }
        }

        // If $result_flag still is 1 (no errors were found, submissions are not too quick), try to submit the review.
        if ($result_flag == 1)
        {
            $this->review_insert($post_array);

            // Errors are caught by review_submit::review_insert and passed to review_submit::insert_err. Check that
            // value. If an error was found, set $result_flag to 4.
            if ($this->insert_err !== null)
            {
                $result_flag = 4;
            }
        }

        // If this is an Ajax request, assign the $result_flag to $review_submit::$ajax_result. The flag will
        // be sent back to the Ajax request as a response.
        if ($is_ajax !== false)
        {
            $this->ajax_result = $result_flag;
            // Stop execution of the rest of this function.
            return;
        }

        // If the submission was not successful, assign $_SESSION error variables.
        if ($result_flag !== 1)
        {
            $this->assign_sessions($post_array);
        }

        // Execute follow up actions based on the value of $result_flag.
        switch ($result_flag)
        {
            case 1:
                // No errors were found. Process submission.
                session_destroy();
                $redirect = "$url?thankyou";
                break;
            case 2:
                // There was one or more validation errors.
                $redirect = "$url";
                break;
            case 3:
                // Submits are being made too quickly. Add a 'timeout' array element to review_submit::post_array.
                // This will output a message for the Rigby user when they're redirected there.
                $redirect = "$url?tooquick";
                break;
            case 4:
                // There was an SQL error.
                $redirect = "$url?problem";
                break;
            default:
                // Undefined problems.
                $redirect = "$url?problem";
                break;
        }
        header("Location: $redirect");
    }

    public function return_ajax_result()
    {
        return $this->ajax_result;
    }


    /**
     * Try and insert the review by create a review_insert object. Pass arguments
     * to the object from $this->post_array.
     *
     * If sql errors were found when running the prepared statement, errors are passed to
     * $this->insert_err.
     *
     * @param array $post_array Review data collected in class __constructor
     */
    protected function review_insert(array $post_array)
    {
        $email      = $post_array['email'];
        $hidden     = $post_array['hidden'];
        $ip         = $post_array['ip'];
        $name       = $post_array['name'];
        $product    = $post_array['product'];
        $rev_txt    = $post_array['comment'];
        $star_rev   = $post_array['star_rev'];
        $title      = $post_array['title'];

        $worker = new review_insert($email, $hidden, $ip, $name, $product, $rev_txt, $star_rev, $title);
        $err = $worker->return_error_sql();

        switch ($err) {
            case null:
                break;
            default:
                $this->insert_err = $err;
                break;
        }
    }

    /**
     * Loops through $post_array and checks if each passed validation.
     *
     * If it didn't, the constructor has assigned -1 to the variable.
     * $key and $value are passed to the $_SESSION array to be used to re-populate
     * the form if the user needs to be re-directed back to the form.
     *
     * @param $post_array array
     */
    protected function assign_sessions(array $post_array)
    {
        foreach ($post_array as $key => $value)
        {
            if ($value === -1) {
                $_SESSION[$key] = -1;
            } else {
                $_SESSION[$key] = $value;
            }
        }
    }

}


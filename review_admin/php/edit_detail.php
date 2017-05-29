<?php
require_once('edit_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to make edits to review records at ../detail_edit.php
 *
 * This class executes the PDO query defined in edit_detail::__constructor() to update a review record from
 * star_reviews.sql. It's meant to provide more editing options than is available through the 'quick edit'
 * functionality at ../reviews.php.
 *
 * On a successful update, the Rigby user receives a 'Review is updated!' message. On failure, the Rigby user
 * receives feedback about what failed field validation.
 *
 * Both success and failure messages are set in $_SESSION. These messages are destroyed each time the class is called,
 * so they can be replaced with any new messages created when the class is run.
 *
 * The Rigby user is always re-directed to ../edit_detail.php with the previous $_GET values set in the URL.
 *
 */
class edit_detail extends edit_abstract {

    /**
     * @var string Holds values from a hidden input used to pass $_GET values from the URL. The value is used
     * to build a new URL that will return the Rigby user to the same search results they used to find the review record.
     */
    protected $get_vars;

    public function __construct() {

        // Parent constructor sets Rigby Username and Password. Check admin privileges.
        parent::__construct();

        $this->get_vars = isset($_POST['get_vars']) ? $_POST['get_vars'] : '';

        $this->pdo_array['title']   = isset($_POST['title'])  ? $this->validate_input($_POST['title'],  60,   'title',   0)                         : '';
        $this->pdo_array['name']    = isset($_POST['name'])   ? $this->validate_input($_POST['name'],   30,   'name',    0)                         : '';
        $this->pdo_array['email']   = isset($_POST['email'])  ? $this->validate_input($_POST['email'],  50,   'email',   0, FILTER_VALIDATE_EMAIL)  : '';
        $this->pdo_array['ip']      = isset($_POST['ip'])     ? $this->validate_input($_POST['ip'],     32,   'ip',      0, FILTER_VALIDATE_IP)     : '';
        $this->pdo_array['cont']    = isset($_POST['cont'])   ? $this->validate_input($_POST['cont'],   1000, 'content', 0)                         : '';
        $this->pdo_array['reply']   = isset($_POST['reply'])  ? $this->validate_input($_POST['reply'],  1000, 'reply',   1)                         : '';
        $this->pdo_array['hidden']  = isset($_POST['hidden']) ? $this->validate_hidden($_POST['hidden'])                                            : '';
        $this->pdo_array['date']    = isset($_POST['date'])   ? $this->validate_date($_POST['date'])                                                : '';
        $this->pdo_array['stars']   = isset($_POST['stars'])  ? $this->validate_stars($_POST['stars'])                                              : '';
        $this->pdo_array['id']      = isset($_POST['id'])     ? $this->validate_numeric($_POST['id'])                                               : '';

        $this->query = "UPDATE star_reviews SET title=?, name=?, email=?, ip=?, cont=?, reply=?, hidden=?, date=?, stars=? WHERE id=?";

        // Try to execute PDO review update.
        // - Update will not be executed if validation errors are found.
        // - If the PDO execution fails, $this->problems['update_error'] is populated.
        $this->try_update($this->query, $this->pdo_array, $this->problems);

        $this->set_input_sessions($this->pdo_array);

        // Clear previous error messages.
        $this->unset_error_messages();

        $this->post_processing($this->problems);;

        $this->set_header($this->get_vars, $this->pdo_array['id']);
    }

    /**
     * Convert values from $pdo_array into $_SESSION variables. These are displayed
     * in field inputs when the Rigby user returns to ../detail_edit.php
     *
     * @param array $pdo_array
     */
    protected function set_input_sessions(array $pdo_array) {
        foreach ($pdo_array as $key => $value) {
            $var_key = 'post_'.$key;
            $_SESSION[$var_key] = $value;
        }
    }

    /**
     * Unset any previous $_SESSION error message. Prepares $_SESSION array for new errors.
     *
     * Called in:
     * - edit_detail::__constructor()
     */
    protected function unset_error_messages() {
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, '_prob') == true) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Sets a success message after removing any previously set error messages in $_SESSION
     *
     * Called in:
     * - edit_abstract::post_processing()
     */
    protected function set_success_message() {
        $_SESSION['success'] = 'Record has been updated.';
    }

    /**
     * Converts $problems array to $_SESSION error messages.
     *
     * Called in:
     * - edit_abstract::post_processing()
     *
     * @param array $problems
     * @return void
     */
    protected function set_error_messages(array $problems) {
        foreach ($problems as $key => $value) {
            $session_index = $key . '_prob';
            $_SESSION[$session_index] = $value;
        }
    }

    /**
     * Builds URL and sends Rigby User to the correct ../detail_edit.php
     *
     * The $get_vars string is set by a hidden input found on the ../detail_edit.php form. It's used to build the
     * 'Go back to the reviews page' link that retains any $_GET values (search criteria). It also sets the browser to
     * scroll to the review record that's just been edited.
     *
     * @param $get_vars string
     * @param $id integer ID for the review record in star_reviews.sql
     */
    protected function set_header ($get_vars, $id) {
        $url      = '../detail_edit.php';
        $get_vars = trim($get_vars);
        $id       = trim($id);

        if ($id !== '' || $get_vars !== '') {
            $url .= '?';
        }
        if ($get_vars !== '') {
            $url .= "$get_vars";
        }
        if ($id !== '') {
            $url .= "#row_$id";
        }
        $set_url = rtrim($url, '&');
        header("Location: $set_url");
    }
}
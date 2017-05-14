<?php
require_once('../../rigby_root.php');
require_once('edit_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to do simple edits when viewing reviews at ../reviews.php.
 *
 * Edits are performed by expanding a given review and making changes to the data in the form inputs and clicking on
 * 'Update.' The update is performed by making an ajax call to the form action {edit_quick_act.php}.
 *
 * The class should never be accessed without an Ajax call. If a user disabled Javascript and tries to access Rigby
 * functionality that uses edit_quick.php, they'll be re-directed to ../edit_detail.php.
 *
 * On a successful update, edit_quick::ajax_reply will be set to '1'. On failure, the class will build a json encoded
 * string with data about which fields failed validation. That string is passed back to the Ajax call and used to
 * provide feedback to the Rigby user.
 */
class edit_quick extends edit_abstract {

    /**
     * Set in edit_quick::__constructor()
     *
     * @var bool Flag is TRUE if $_POST['ajax_submit'] is set. Else, FALSE
     */
    protected $is_ajax;

    /**
     * Holds the response sent to the Ajax call.
     *
     * Set in edit_abstract::post_processing(). If update is successful, $ajax_reply will be set to '1'. If it fails,
     * $ajax_reply will be set to a json_encoded string that holds data for which fields failed validation.
     *
     * @var null
     */
    protected $ajax_reply = NULL;

    public function __construct() {

        // Parent constructor sets Rigby Username and Password. Check admin privileges.
        parent::__construct();

        $this->pdo_array['title']   = isset($_POST['title'])  ? $this->validate_input($_POST['title'],  60,   'title',   0)                         : '';
        $this->pdo_array['name']    = isset($_POST['name'])   ? $this->validate_input($_POST['name'],   30,   'name',    0)                         : '';
        $this->pdo_array['email']   = isset($_POST['email'])  ? $this->validate_input($_POST['email'],  50,   'email',   0, FILTER_VALIDATE_EMAIL)  : '';
        $this->pdo_array['cont']    = isset($_POST['cont'])   ? $this->validate_input($_POST['cont'],   1000, 'content', 0)                         : '';
        $this->pdo_array['hidden']  = isset($_POST['hidden']) ? 1 : 0;
        $this->pdo_array['stars']   = isset($_POST['stars'])  ? $this->validate_stars($_POST['stars'])                                              : '';
        $this->pdo_array['id']      = isset($_POST['id'])     ? $this->validate_numeric($_POST['id'])                                               : '';

        $this->query = "UPDATE star_reviews SET title=?, name=?, email=?, cont=?, hidden=?, stars=? WHERE id=?";

        $this->is_ajax = isset($_POST['ajax_submit']) ? TRUE : FALSE;

        $this->check_if_ajax($this->is_ajax, $this->pdo_array['id']);

        $this->try_update($this->query, $this->pdo_array, $this->problems);

        $this->post_processing($this->problems);
    }

    /**
     * Set header to ../detail_edit.php if the class is used without an Ajax call.
     *
     * The class shouldn't be called unless it's through Ajax.
     *
     * @param bool $is_ajax
     * @param $id integer ID number for the review record in star_reviews.sql
     */
    protected function check_if_ajax($is_ajax, $id) {
        switch ($is_ajax) {
            case FALSE:
                $url = '../detail_edit.php';
                if ($id !== '') {
                    $url .= "?id=$id";
                }
                header("Location: $url");
                break;
            default:
                break;
        }
    }

    /**
     * Sets edit_quick::$ajax_reply to 1. This will let the Ajax call know the update was successful.
     */
    protected function set_success_message() {
        $this->ajax_reply = 1;
    }

    /**
     * Sets edit_quick::$ajax_reply with a json encoded string for fields that failed validation.
     *
     * The class will return the json encoded string to the Ajax call, which will use the decoded
     * array to set errors so the Rigby user will know what fields to correct.
     *
     * @param array $problems
     * @return void
     */
    protected function set_error_messages(array $problems) {

        // Initialize array to hold $keys from $problems array.
        $ajax_errors = array();

        // Populate $ajax_errors with $problems keys.
        foreach ($problems as $key => $problem) {
            $ajax_errors[] = $key;
        }
        // Set edit_quick::$ajax_reply with JSON encoded string from $ajax_errors.
        $json_errors = json_encode($ajax_errors);
        $this->ajax_reply = $json_errors;
    }

    /**
     * Public access for edit_quick::$ajax_reply
     * @return integer|string Returns 1 if update was successful, Else returns a json_encoded string.
     */
    public function get_ajax_reply() {
        return $this->ajax_reply;
    }
}

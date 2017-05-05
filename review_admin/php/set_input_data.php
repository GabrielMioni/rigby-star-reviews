<?php

require_once('get_review_by_id.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class set_input_data
 *
 * Sets values to be displayed in the HTML form found in ../detail_edit.php.
 *
 * If the Rigby user has tried to edit the form but failed validation the class will overwrite the appropriate
 * array element with the user supplied data.
 *
 * @uses get_review_by_id Used to get review data
 *
 */
class set_input_data {
    protected $input_array = array();

    public function __construct() {
        $this->input_array = $this->set_review_data();
        $this->set_sess_input($this->input_array);
    }

    /**
     * Gets review data based on the $_GET['id'] value. If review data is found, overwrite the already initialized
     * set_input_data::$input_array. Else, return an array with keys matching the review database but with whitespace
     * values.
     *
     * @return array If review data is found, return array of review data. Else, nothing happens.
     */
    protected function set_review_data() {
        $get_review_data = new get_review_by_id();
        if (!empty($get_review_data)) {
            return $get_review_data->return_review_data();
        } else {
            return $this->init_input_array();
        }
    }

    /**
     * Makes sure that set_input_data::$input_array will always be formatted with keys matching the
     * review data from SQL.
     *
     * @return array Array with keys matching review data set with whitespace values.
     */
    protected function init_input_array() {
        $tmp = array();
        $empty = '';
        $tmp['id']     = $empty;
        $tmp['title']  = $empty;
        $tmp['name']   = $empty;
        $tmp['email']  = $empty;
        $tmp['cont']   = $empty;
        $tmp['ip']     = $empty;
        $tmp['hidden'] = $empty;
        $tmp['date']   = $empty;
        $tmp['stars']  = $empty;

        return $tmp;
    }

    /**
     * Overwrite set_input_data::$input_array with any data the Rigby user has added. This is necessary in case
     * the user has added badly formatted data on the detail edit form and is returned to the form to correct
     * mistakes.
     *
     * @param array $input_array
     */
    protected function set_sess_input(array &$input_array) {
        if(isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                $chk = strpos($key, 'post_');
                if ($chk !== FALSE) {
                    $key_format = str_replace('post_', '', $key);
                    $input_array[$key_format] = $value;
                }
            }
        }
    }

    /**
     * @return array Review data that includes data from SQL as well as any $_SESSION values from the edit form
     */
    public function return_input_array() {
        return $this->input_array;
    }
}
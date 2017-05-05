<?php
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class get_review_by_id
 *
 * Builds a PDO and retrieves individual reviews based on the ID value set in by $_GET['id'].
 *
 * @used-by set_input_data.php Retrieves review data for display in ../detail_edit.php
 */
class get_review_by_id {
    /**
     * @var int Set by $_GET['id']
     */
    protected $id;

    /**
     * @var array Holds exceptions if encountered while running the PDO.
     */
    protected $problems = array();

    /**
     * @var array SQL data from the review associated with the get_review_by_id::$id.
     */
    protected $review_data_raw = array();

    /**
     * @var array Holds review data formatted for HTML display.
     */
    protected $review_data_out = array();

    public function __construct() {
        $this->id = isset($_GET['id']) ? $_GET['id'] : 'x';

        $this->review_data_raw = $this->get_review($this->id);
        $this->review_data_out = $this->prepare_data($this->review_data_raw);
    }

    /**
     * Gets SQL review data based on ID that's set by $_GET['id'].
     *
     * @param $id int The ID for the review being queried.
     * @return array   Returns review data if data is found. Else, returns an empty array.
     */
    protected function get_review($id) {
        $query = "SELECT * FROM star_reviews WHERE id=?";

        try {
            $review_data_raw = sql_pdo::run($query, [$id])->fetch();
        } catch (Exception $ex) {
            $this->problems = $ex;
            $review_data_raw = array();
        }
        return $review_data_raw;
    }

    /**
     * Builds an array of review data and formats content for HTML display.
     *
     * @param $raw_review_data array Data from a single review.
     * @return array Formatted review data.
     */
    protected function prepare_data($raw_review_data) {
        $tmp = array();
        if (empty($raw_review_data)) {
            return $tmp;
        }
        foreach ($raw_review_data as $key => $value) {
            if ($key == 'cont') {
                $tmp[$key] = $this->process_cont($value);
            } else {
                $tmp[$key] = $value;
            }
        }
        return $tmp;
    }

    /**
     * Formats review text's content for display.
     *
     * @param $cont string String from the review's content.
     * @return string Returns formatted content.
     */
    protected function process_cont($cont) {
        $out = rtrim($cont, '<br><br>');
        return str_replace("<br>", "\n", $out);
    }

    /**
     * @param $cont
     * @return string
     * @deprecated
     */
    protected function x_process_cont($cont) {

        $cont = '<p>'.$cont;
        $data = preg_replace('#(?:<br\s*/?>\s*?){2,}#', '</p><p>', $cont);
        return rtrim($data, '<p></p>');

    }

    /**
     * @return array Public access for formatted review data
     */
    public function return_review_data() {
        return $this->review_data_out;
    }
}
<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class build_return_url
 *
 * Sets a URL that includes a jump link so the Rigby user doesn't have to track back through a large review table to
 * find where they originally chose to do a detail edit on a review.
 *
 * On reviews.php each row in the review table includes an anchor. Example: &lt;a name="row_176"&gt;176&lt;/a&gt;
 *
 * The URL will include $_GET values from the review search criteria and an appropriate jump link for the ID of the review being edited.
 *
 * It will look something like:
 *
 * http://your_web_page.com/rigby/review_admin/reviews.php?title_search=&name_search=Bobo%25&email_search=&ip_search=&date_range=date_range&date_single=&date_start=&date_end=&star-5=on&page_p=50&search=Search#row_176
 *
 * Used in:
 * - ../detail_edit.php
 */
class build_return_url {

    /**
     * @var int Holds id of the review being edited. Set by class constructor.
     */
    protected $id;

    /**
     * @var string Set at reviews.php.
     */
    protected $review_url;

    /**
     * @var string Holds a string formatted from $_GET values.
     */
    protected $get_values;

    /**
     * @var string Holds the completed URL to return the Rigby user to the place in reviews.php where they originally
     * chose to do a detailed edit on a review.
     */
    protected $url = 'reviews.php';

    /**
     * @param $id int Id for the review being edited.
     */
    public function __construct($id) {
        $this->id = $id;
        $this->get_values = $this->set_get_values();

        $this->build_url($this->id, $this->get_values, $this->url);
    }

    /**
     * Formats $_GET values into a string that can be appended to set_get_vals::$return_rul. Necessary so that
     * the Rigby user is returned to the same review table results.
     *
     * @return string Formatted $_GET values for the return URL.
     */
    protected function set_get_values() {
        $get_string = '';
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key !== 'id') {
                    $get_string .= "$key=$value&";
                }
            }
        }
        return rtrim($get_string, '&');
    }

    /**
     * Formats a return URL based on the $id and $get_vals arguments.
     *
     * The $return_url is appended with $id and $get_vals by reference.
     *
     * @param $id int Id for the review being edited.
     * @param $get_values string String set with $_GET values.
     * @param $url string The URL used to return the Rigby user to reviews.php
     */
    protected function build_url($id, $get_values, &$url) {
        if ($get_values !== '') {
            $url .= "?$get_values";
        }
        $url .= '#row_' . $id;
    }

    /**
     * Public access for detail_edit.php::url.
     *
     * @return string
     */
    public function return_url() {
        return $this->url;
    }
}
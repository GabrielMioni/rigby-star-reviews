<?php

/*
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require_once("$root/projects/star-reviews/php/review_return.php");
require_once('dash_abstract.php');
*/

require_once(RIGBY_ROOT . '/php/review_return.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */


/**
 * Displays recent reviews in the Dashboard Recent Activity widget found at
 * star-reviews/index.php
 */
class dash_activity extends dash_abstract {

    /**
     * @var integer Defines the start in the prepared statement.
     */
    protected $start;

    /**
     * @var array Holds review data from prepared statement.
     */
    protected $review_array = array();

    /**
     * dash_activity constructor.
     * @param $start integer
     */
    public function __construct($start) {

        $this->start = $start;
        $this->review_array = $this->get_review($this->start);

        $this->widget = $this->build_widget();
    }

    /**
     * Returns review data. If no review data is present the method will return
     * an empty array.
     *
     * @param $start integer Defines start of SQL prepared statement. Passed to
     * class review_return().
     * @return array
     */
    protected function get_review($start) {
        $get_reviews = new review_return('', '', $start, 5);
        return $this->review_array = $get_reviews->return_review_array();
    }

    /**
     * Builds HTML for the recent reviews widget using review data
     * held in $this->review_array.
     *
     * @return string HTML for the recent activity widge
     */
    protected function build_widget() {
        $review_array = $this->review_array;
        
        $widget = " <div class='box'>
                        <h3>Recent Activity</h3>
                        <div class='box_toggle'>
                            <div class='tog toggle_up'></div>
                        </div>
                        <div class='box_cont'>
                            <div class='activity'>";
        foreach ($review_array as $review) {
            $email = $review['email'];
            $stars = $review['stars'];
            $date  = date('m/d/y - g:ia', strtotime($review['date']));
            $cont  = $review['cont'];
            
            
            $star_div = '';
            
            for ($s = 0 ; $s < $stars ; ++$s) {
                $star_div .= "<div class='star_full'></div>";
            }
                    
            $widget .=      "<div class='review_wrap'>
                                <div class='email_col'>
                                    <div class='title'>Email</div>
                                    <div class='rev_email'>$email</div>                                        
                                </div>
                                <div class='star_col'>
                                    <div class='title'>Stars</div>
                                    <div class='rev_stars'>
                                        $star_div
                                    </div>                                        
                                </div>
                                <div class='date_col'>
                                    <div class='title'>Date</div>
                                    <div class='rev_date'>$date</div>
                                </div>
                                <div class='content_col'>
                                    <div class='rev_cont'>
                                        $cont
                                    </div>                                        
                                </div>
                            </div>";
        }
        $widget .=          "</div>
                        </div>
                    </div>";
        return $widget;
    }
}
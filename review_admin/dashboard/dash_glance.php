<?php

require_once('dash_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Builds HTML for the 'At a Glace' widget found at star-reviews/index.php
 */
class dash_glance extends dash_abstract {

    /**
     * @var string Human readable start date. Used to define $this->today and $this->yesterday using
     * $this->set_today_yesterday(). Set by class constructor.
     */
    protected $start_date;

    /**
     * @var string Human readable date for today. Set by $this->set_today_yesterday(). Defines the latest
     * SQL record that should be retrieved in the prepared statement.
     *
     */
    protected $today;

    /**
     * @var string Human readable date for today. Set by $this->set_today_yesterday(). Defines the earliest
     * SQL record that should be retriieved in the prepared statement.
     */
    protected $yesterday;

    /**
     * @var integer Holds the review count for today.
     */
    protected $count_today;

    /**
     * @var integer Holds the review count for yesterday.
     */
    protected $count_yesterday;

    /**
     * @var integer Holds total counts (time/date agnostic).
     */
    protected $count_total;

    /**
     * @var float Holds the average review rating.
     */
    protected $average;

    /**
     * @var string HTML for the widget.
     */
    protected $widget;

    public function __construct($start_date) {
        $this->start_date = $start_date;

        /* Define $this->yesterday and $this->today */
        $this->set_today_yesterday($this->start_date);

        /* Define data used to build the widget */
        $this->count_today      = $this->get_count_today($this->today);
        $this->count_yesterday  = $this->get_count_yesterday($this->yesterday, $this->today);
        $this->count_total      = $this->get_count_total();
        $this->average          = $this->get_average($this->count_total);

        /* Set HTML for the widget */
        $this->widget = $this->build_widget();
    }

    /**
     * Defines $this->today and $this->yesterday by looking at $start_date.
     *
     * @param $start_date string Human readable date.
     */
    protected function set_today_yesterday($start_date) {
        $midnight_unix  = strtotime(date('m/d/y', strtotime($start_date)));
        $yesterday_unix = strtotime('-1 day', $midnight_unix);

        $this->today     = date($this->sql_date_format, $midnight_unix);
        $this->yesterday = date($this->sql_date_format, $yesterday_unix);
    }

    /**
     * Get total counts for today's date as defined by $this->start_date.
     *
     * @param $today
     * @return bool|string
     */
    protected function get_count_today($today) {
        $query = "SELECT COUNT(*) FROM star_reviews WHERE date > ?;";
        $results = $this->process_query_column($query, [$today]);

        return $results;
    }

    /**
     * Get total counts for yesterday's date as defined by $this->start_date
     *
     * @param $yesterday string Human readable date. Sets date > in PDO.
     * @param $today string Human readable date. Sets date < in PDO.
     * @return bool|string Results from PDO.
     */
    protected function get_count_yesterday($yesterday, $today) {
        $query = "SELECT COUNT(*) FROM star_reviews WHERE date > ? AND date < ?;";
        $yesterday_total = $this->process_query_column($query, [$yesterday, $today]);
        return $yesterday_total;
    }

    /**
     * Get total count for all reviews.
     *
     * @return bool|string
     */
    protected function get_count_total() {
        $query = "SELECT COUNT(*) FROM star_reviews;";
        $all_total = $this->process_query_column($query, []);
        return $all_total;
    }

    /**
     * @param $count_total
     * @return integer|float
     */
    protected function get_average($count_total)
    {
        $query = "SELECT SUM(stars) FROM star_reviews;";
        $sum = $this->process_query_column($query, []);

        if ($sum == 0)
        {
            $avg = 0;
        } else {
            $avg = $sum / $count_total;
        }

        return number_format((float)$avg, 2, '.', '');
    }
    /*
        protected function process_query_column($query) {
            $return = sql_pdo::run("$query")->fetch();
            return $return;
        }
    */

    /**
     * Build HTML for the widget using data passed to the method.
     *
     * @return string HTML for the widget
     */
    protected function build_widget() {
        $count_total     = $this->count_total;
        $count_today     = $this->count_today;
        $count_yesterday = $this->count_yesterday;
        $average         = $this->average;

        $date_format    = 'D m/d';
        $today_date     = date($date_format, strtotime($this->today));
        $yesterday_date = date($date_format, strtotime($this->yesterday));

        $widget  =   "<div class='box'>
                                <h3>At A Glance</h3>
                                <div class='box_toggle'>
                                    <div class='tog toggle_up'></div>
                                </div>
                                <div class='box_cont'>
                                    <div class='row'>
                                        <span class='row_title'>Total Reviews</span>
                                        <div class='row_var'>$count_total</div>
                                    </div>
                                    <div class='row'>
                                        <span class='row_title'>Review Average</span>
                                        <div class='row_var'>$average</div>
                                    </div>
                                    <div class='row'>
                                        <span class='row_title'>Reviews Today [$today_date]</span>
                                        <div class='row_var'>$count_today</div>
                                    </div>
                                    <div class='row'>
                                        <span class='row_title'>Reviews Yesterday [$yesterday_date]</span>
                                        <div class='row_var'>$count_yesterday</div>
                                    </div>
                                </div>
                            </div>";
        return $widget;
    }
}
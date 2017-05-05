<?php

require_once('dash_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Builds an HTML bar graph displaying review counts for each day
 * of a given week.
 *
 * Extends abstract class dash_abstract.
 *
 * @param $start_date string Today's date. Used to build an array of dates for the week starting with Monday.
 *
 * Used in star-reviews/review_admin/index.php
 */
class dash_bars extends dash_abstract {

    /**
     * Human readable start date.
     *
     * @var string
     */
    protected $start_date;

    /**
     * Human readable date for the proceeding Monday from today's date.
     *
     * @var string
     */
    protected $monday;

    /**
     * Array holds human readable dates for each day of the week, starting from Monday.
     *
     * @var string[]
     */
    protected $week_array    = array();

    /**
     * Array holds queries to get total review counts for each day of the week.
     *
     * @var string[]
     */
    protected $count_queries = array();

    /**
     * Array of count for each day of the week.
     *
     * Set by $this->get_counts()
     *
     * @var int[]
     */
    protected $counts        = array();

    /**
     * Each array element holds 'intervals' used in the bar graph y-axis.
     * Containing array always starts with 0 and then 5 more integers.
     *
     * Eg.: [0,5,10,15,20,25]
     *
     * Set by $this->build_intervals()
     *
     * @var int[]
     */
    protected $y_axis_labels     = array();

    /**
     * Array elements used to fill each bar. This is used to build inline css for the bar chart.
     *
     * Eg. $fill_perc[0] = .45 would create the following HTML element: <div class="fill" style="height:45%;"></div>
     *
     * Set by $this->build_fill_perc()
     *
     * @var float[]
     */
    protected $fill_percent     = array();

    /**
     * Holds HTML strings used for the bottom z-axis date labels in the bar graph.
     *
     * Eg.: 2/14<br>Tues
     *
     * @var string[]
     */
    protected $day_display   = array();


    /**
     * bar_glance constructor.
     * @param $start_date string Human readable date for today.
     */
    public function __construct($start_date) {
        $this->start_date = $start_date;

        /*  Initialize data used to build the bar graph widget. */
        $this->monday        = $this->find_monday($this->start_date);
        $this->week_array    = $this->build_week($this->monday);
        $this->counts        = $this->build_counts($this->week_array);

        /* These arrays will be used to construct the bar graph */
        $this->y_axis_labels = $this->set_y_axis_labels($this->counts);
        $this->fill_percent  = $this->set_fill_percent($this->y_axis_labels);
        $this->day_display   = $this->build_day_display($this->week_array);

        /* Build the HTML bar graph. */
        /** @var abst_glance_data $this */
        $this->widget = $this->build_widget();
    }

    /**
     * Returns human readable date for Monday.
     *
     * Loops 7 times. Each loop removes 24 hours. Populates an array using the short day name
     * as the index and sets array element value to the human readable date for the day.
     *
     * When done, return human readable date for Monday.
     *
     * @param $start_date string Today's date. Set by class constructor.
     * @return string Human readable date for Monday.
     */
    protected function find_monday($start_date) {
        $unix_start = strtotime($start_date);

        $week_arr = array();

        for ($x = 0 ; $x < 7 ; ++$x) {
            $day = date('D', $unix_start);
            $week_arr[$day] = date($this->sql_date_format, $unix_start);
            $unix_start -= 86400;
        }
        return $week_arr['Mon'];
    }

    /**
     * Returns array of week date starting with the date provided
     * in the method argument.
     *
     * @param $monday_date string Human readable date for Monday
     * @return array Returns array of week dates starting with
     */
    protected function build_week($monday_date) {

        // Initialize array to accept subsequent dates.
        $week_arr = array();

        $unix_monday = strtotime($monday_date);
        for ($x = 0 ; $x < 7 ; ++$x) {
            $week_arr[] = date($this->sql_date_format, $unix_monday);
            $unix_monday += 86400;
        }
        return $week_arr;
    }

    /**
     * Gets counts for each day of the week, defined by argument $week_array.
     *
     * @param array $week_array
     * @return array Counts for each day of the week.
     */
    protected function build_counts(array $week_array) {
        $count_array = array();

        foreach ($week_array as $sql_day) {
            $unix_day = strtotime($sql_day);
            $unix_nxt = $unix_day + 86400;
            $sql_nxt  = date($this->sql_date_format, $unix_nxt);

            $query = "SELECT COUNT(*) FROM star_reviews WHERE date > ? AND date < ?;";
            $count_result = $this->process_query_column($query, [$sql_day, $sql_nxt]);
            switch ($count_result) {
                case FALSE:
                    $count_array[] = 0;
                    break;
                default:
                    $count_array[] = $count_result;
                    break;
            }
        }
        return $count_array;
    }

    /**
     * Sets the labels for the y-axis.
     *
     * @param array $counts
     * @return array
     */
    protected function set_y_axis_labels(array $counts) {
        $high_count = max($counts);

        $div = $high_count / 5;
        $interval = $this->round_up_five($div);

        // First array element will always be 0.
        $interval_array[] = 0;
        for ($x = 1; $x <= 5 ; ++$x) {
            $interval_array[] = $interval * $x;
        }
        return $interval_array;
    }

    /**
     * Rounds argument $n up to nearest 5.
     *
     * @param $n integer
     * @param int $x
     * @return float
     */
    function round_up_five($n,$x=5) {
        return (ceil($n)%$x === 0) ? ceil($n) : round(($n+$x/2)/$x)*$x;
    }

    /**
     * Returns array with values used to set fill percentage on y-axis of bar graph.
     *
     * Eg. $fill_percent_array[0] = .45 would create the following HTML element: <div class="fill" style="height:45%;"></div>
     *
     * @param array $y_axis_labels
     * @return array Percentages to fill y-axis bars.
     */
    protected function set_fill_percent(array $y_axis_labels) {
        $max_interval = max($y_axis_labels);
        $counts = $this->counts;
        $percent_formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);

        $fill_percent_array = array();

        foreach ($counts as $rev_count) {
            if ($rev_count !== 0 && $max_interval !== 0) {
                $decimal = $rev_count / $max_interval;
            } else {
                $decimal = 0;
            }
            $fill_percent_array[] = $percent_formatter->format($decimal);
        }
        return $fill_percent_array;
    }

    /**
     * Sets array with labels for the x-Axis. Format is: Tues &lt;br&gt; 2/14
     *
     * @param array $week_array
     * @return array Labels for the x-axis
     */
    protected function build_day_display(array $week_array) {
        $display_array = array();
        foreach ($week_array as $sql_day) {
            $display_str = date('D*m/d', strtotime($sql_day));
            $display_array[] = str_replace('*', '<br>', $display_str);
        }
        return $display_array;
    }

    /**
     * Returns HTML for bar graph widget.
     *
     * Publicly accessible through $this->return_widget()
     *
     * @return string Bar graph HTML.
     */
    protected function build_widget() {
        $intervals   = array_reverse($this->y_axis_labels);
        $fill_perc   = $this->fill_percent;
        $day_display = $this->day_display;

        $widget =   "<div class='box'>
                        <h3>Weekly Report</h3>
                        <div class='box_toggle'>
                            <div class='tog toggle_up'></div>
                        </div>
                        <div class='box_cont'>
                            <div class='go left'><span class='icon'><i class='fa fa-caret-left' aria-hidden='true'></i>
                                </span></div>
                            <div class='go right'><span class='icon'><i class='fa fa-caret-right' aria-hidden='true'></i></span></div>
                            <div class='bar_graph'>
                                <div class='bottoom_bar'>
                                    <div class='week_wrapper'>
                                        <div class='count_label'>";
        foreach ($intervals as $set_interval) {
            $interval_div = "<div class='line'><div class='num'>$set_interval</div><div class='count_line'></div></div>";
            $widget .= $interval_div;
        }

        $widget .=                  "</div>"; // Close week_wrapper

        foreach ($day_display as $key => $day) {
            $day_div =  "<div class='days'>
                            <div class='date'>$day</div>
                            <div class='count_bar'>
                                <div class='fill_wrap'>
                                    <div class='fill' style='height:".$fill_perc[$key].";'></div>
                                </div>                                                    
                            </div>
                        </div>";
            $widget .= $day_div;
        }
        $widget .=                  "</div>
                                </div>
                            </div>
                        </div>
                    </div>";
        return $widget;
    }
}

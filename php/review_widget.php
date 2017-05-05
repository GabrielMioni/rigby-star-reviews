<?php

require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');

class review_widget
{
    /** @var string Holds product code. */
    protected $product;

    /** @var array Holds count of reviews based on ratings 1-5 */
    protected $rating_count_array = array();

    /** @var int int Holds total count for all reviews */
    protected $review_count_total;

    /** @var NumberFormatter Holds NumberFormatter object used to format percentage */
    protected $percent_formatter;

    protected $url;

    /** @var string Holds HTML for review widget. */
    protected $widget_html;

    /**
     * review_widget constructor.
     * @param $product string Product code being requested
     */
    public function __construct($product)
    {
        $this->percent_formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);
        $this->product = ($product === '') ? '%' : strtoupper($product);
        $this->rating_count_array = $this->get_counts($this->product);
        $this->review_count_total = $this->get_review_count($this->product);
        $this->url = strtok($_SERVER["REQUEST_URI"],'?');
        $this->widget_html = $this->build_widget($this->rating_count_array, $this->review_count_total, $this->url, $this->percent_formatter);
    }

    protected function get_counts($product)
    {
        $tmp = array();
        for ($r = 1, $i = 0 ; $r <= 5 ; ++$r, ++$i)
        {
            $query = "SELECT count(*) FROM star_reviews WHERE product LIKE ? AND stars=? AND hidden != 1";
            $tmp[$i] = sql_pdo::run($query, [$product, $r])->fetchColumn();
        }
        return $tmp;
    }

    protected function get_review_count($product)
    {
        $query = "SELECT count(*) FROM star_reviews WHERE product LIKE ? AND hidden != 1";
        return sql_pdo::run($query, [$product])->fetchColumn();
    }

    protected function build_widget(array $review_count_array, $review_totals, $url, NumberFormatter $formatter_obj)
    {
        $rev_fill_5 = $formatter_obj->format($review_count_array[4] / $review_totals);
        $rev_fill_4 = $formatter_obj->format($review_count_array[3] / $review_totals);
        $rev_fill_3 = $formatter_obj->format($review_count_array[2] / $review_totals);
        $rev_fill_2 = $formatter_obj->format($review_count_array[1] / $review_totals);
        $rev_fill_1 = $formatter_obj->format($review_count_array[0] / $review_totals);

        $url_5 = $url . '?rating=5';
        $url_4 = $url . '?rating=4';
        $url_3 = $url . '?rating=3';
        $url_2 = $url . '?rating=2';
        $url_1 = $url . '?rating=1';
        
        $widget_html = "    <div id='review_widget'>
                                <div class='row'>
                                    <span class='rating_title'><a href='$url_5'>5 Stars</a></a></span>
                                    <div id='rate5' class='rating_bar'><div class='fill' style='width: $rev_fill_5;'></div></div>
                                    <span class='rating_tot'>$rev_fill_5</span><span class='rev_count'>$review_count_array[4]</span>
                                </div>
                                <div class='row'>
                                    <span class='rating_title'><a href='$url_4'>4 Stars</a></span>
                                    <div id='rate4' class='rating_bar'><div class='fill' style='width: $rev_fill_4;'></div></div>
                                    <span class='rating_tot'>$rev_fill_4</span><span class='rev_count'>$review_count_array[3]</span>
                                </div>
                                <div class='row'>
                                    <span class='rating_title'><a href='$url_3'>3 Stars</a></span>
                                    <div id='rate3' class='rating_bar'><div class='fill' style='width: $rev_fill_3;'></div></div>
                                    <span class='rating_tot'>$rev_fill_3</span><span class='rev_count'>$review_count_array[2]</span>
                                </div>
                                <div class='row'>
                                    <span class='rating_title'><a href='$url_2'>2 Stars</a></span>
                                    <div id='rate2' class='rating_bar'><div class='fill' style='width: $rev_fill_2;'></div></div>
                                    <span class='rating_tot'>$rev_fill_2</span><span class='rev_count'>$review_count_array[1]</span>
                                </div>
                                <div class='row'>
                                    <span class='rating_title'><a href='$url_1'>1 Stars</a></span>
                                    <div id='rate1' class='rating_bar'><div class='fill' style='width: $rev_fill_1;'></div></div>
                                    <span class='rating_tot'>$rev_fill_1</span><span class='rev_count'>$review_count_array[0]</span>
                                </div>
                                <div class='tod_display'><i><a href='$url'>$review_totals Total Reviews</a></i></div>
                            </div>";
        return $widget_html;
    }

    public function return_widget() {
        return $this->widget_html;
    }
}

//new review_widget('');
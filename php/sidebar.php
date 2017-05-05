<?php

require_once('review_navigate_abstract.php');
require_once('review_return.php');


class sidebar extends review_navigate_abstract {
    protected $start;
    protected $product;
    protected $product_array;

    protected $review_array = array();
    protected $sidebar_html;

    public function __construct($page, $rating, $reviews_per_page, $product, array $product_array)
    {
        parent::__construct($page, $rating, $reviews_per_page);

        $this->start    = $this->set_start($this->page, $this->reviews_per_page);
        $this->product  = $product;
        $this->product_array = $product_array;

        $this->review_array = $this->get_reviews($this->rating, $this->product, $this->start, $this->reviews_per_page);

        $this->sidebar_html = $this->build_sidebar($this->review_array);

    }

    protected function set_start($page, $reviews_per_page)
    {
        return ($page - 1) * $reviews_per_page;
    }

    /**
     * @param $rating
     * @param $product
     * @param $start
     * @param $end
     * @return array
     */
    protected function get_reviews($rating, $product, $start, $end) {
        $get_reviews = new review_return($rating, $product, $start, $end);
        return $get_reviews->return_review_array();

    }

    protected function build_sidebar(array $review_array) {
        $review_cards = '';

        foreach ($review_array as $review) {
            $review_cards .= $this->review_card_formatter($review);
        }

        return $review_cards;
//        return $review_cards;
    }

    protected function review_card_formatter(array $review) {
        $title   = $review['title'];
        $name    = $review['name'];
        $product = $this->return_product($review['product']);
        $cont    = $this->process_cont($review['cont']);
        $stars   = $this->set_stars($review['stars']);
        $date    = $this->format_date($review['date']);
        $reply   = $this->set_reply($review['reply']);

        $review_card = "    <div class='review_card'>
                                <div class='review_lead'>
                                    <div class='rating'>
                                        $stars
                                    </div>
                                    <div class='date_prod'>
                                        <div class='date'>$date</div> - 
                                        <div class='prod'>$product</div>
                                    </div>
                                    <div class='review_title'>
                                        <p>
                                            <b>$title</b>
                                        </p>
                                    </div>
                                    <div class='review_name'>By <i>$name</i></div>
                                </div>
                                <div class='review_content'>$cont</div>
                                $reply
                            </div>";
        return $review_card;
    }

    protected function return_product($product) {
        $return_prod = '';
        $product_array = $this->product_array;

        foreach ($product_array as $key => $value)
        {
            if (strtolower($key) == strtolower($product)) {
                $return_prod = $value;
                break;
            }
        }
        return $return_prod;
    }

    protected function process_cont($cont) {
        $cont = '<p>'.$cont;
        $data = preg_replace('#(?:<br\s*/?>\s*?){2,}#', '</p><p>', $cont);
        return rtrim($data, '<p></p>');
    }

    protected function set_reply($reply) {
        $html = '';

        if ($reply !== null) {
            $reply = $this->process_cont($reply);
            $html .= "<div class='reply'>$reply</div>";
        }
        return $html;
    }

    protected function set_stars($stars) {
        $star_divs = '';
        for ($s = 0 ; $s < $stars ; ++$s) {
            $star_divs .= "<div class='star_full'></div>";
        }
        return $star_divs;
    }

    protected function format_date($date) {
        /* @ review_card formatter */
        $format = 'M j, Y';
        return date($format, strtotime($date));
    }

    /**
     * @return string
     */
    public function getSidebarHtml()
    {
        return $this->sidebar_html;
    }
}

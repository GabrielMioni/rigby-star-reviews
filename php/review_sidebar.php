<?php

require_once('review_return.php');
require_once('review_pagination.php');
require_once('product_array.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to insert review data to reviews.sql
 * 
 * This class builds html to display reviews in a 'card' format.
 * 
 * Arguments are passed to the reivew_return class, which returns an array of
 * results. Those results are formatted into html.
 *
 * @param string  $rating   Sets rating for requested reviews. Whitespace returns all
 *                          reviews including all ratings. An integer returns reviews swith
 *                          a specific rating.
 * @param string  $product  Sets product code for requested reviews. If whitespace
 *                          reviews will include any product.
 * @param integer $start    Sets start
 * @param integer $start_range  Sets start for rows in the reviews.sql table.
 * @param integer $end_range    Sets end for rows in the reviews.sql table.
 * @param array   $prod_array   Accepts product array. Argument is used to get a product's
 *                              long name from its product code (specified by the $product argument).
 */
class review_sidebar {
    protected $rating;
    protected $product;
    protected $start;
    protected $end;
    protected $prod_array;

    /**
     * Array holds review data set by get_reviews();
     *
     * @access	protected
     * @var	array
     */
    protected $reviews = array();
    
    /**
     * Holds html for sidebar. Set with build_sidebar().Returned with return_sidebar().
     *
     * @access	protected
     * @var	array
     */
    protected $sidebar;
    
    public function __construct($rating, $product, $start, $end, $prod_array) {

        $this->rating  = $this->set_rating($rating);
        $this->product = $product;
        $this->start   = $start;
        $this->end     = $end;
        $this->prod_array = $prod_array;

        $this->reviews = $this->get_reviews();
        $this->sidebar = $this->build_sidebar();
    }

    /**
     * If $rating is whitespace, check to see if $_GET['rating'] is set.
     *
     * When $rating is passed to review_return::__construct(), whitespace will set the PDO to search
     * for reviews with any rating.
     *
     * @param $rating string|int Can be left blank
     * @return string|int If $rating is whitespace OR if $_GET['rating'] is unset, return whitespace. Else, return $rating.
     */
    protected function set_rating($rating) {
        if ($rating == '') {
            $out = isset($_GET['rating']) ? $_GET['rating'] : '';
        } else {
            $out = $rating;
        }
        return $out;
    }
    
    /**
    * Passes class arguments to create a new review_return object.
    * Review_return object returns an array. If no review data is found
    * it returns an empty array.
    *
    * @access   protected
    * @return   array
    */
    protected function get_reviews() {
        $rating  = $this->rating;
        $product = $this->product;
        $end     = $this->end;
        $start   = $this->start * $end;
        
        $get_reviews = new review_return($rating, $product, $start, $end);
        return $get_reviews->return_review_array();
    }
    
    /**
    * Uses array data from get_reviews() to build HTML for the sidebar.
    * Each arrary element is processed by review_card_formatter($reviews),
    * which returns HTML 'review cards'.
    *
    * @access   protected
    * @return   string
    */
    protected function build_sidebar() {
        $reviews = $this->reviews;
        
        $review_cards = '';
        
        foreach ($reviews as $rev) {
            $review_cards .= $this->review_card_formatter($rev);
        }
        return $review_cards;
    }

    /**
     * Formats review array elements into HTML 'review cards.' Called in build_sidebar().
     *
     * @access  protected
     * @param   array $review Review data from star_reviews.sql
     * @return  string
     */
    protected function review_card_formatter(array $review) {
        $title   = $review['title'];
        $name    = $review['name'];
        $product = $this->return_product($review['product']);
        $cont    = $review['cont'];
        $reply   = $this->set_reply($review['reply']);
        $stars   = $this->set_stars($review['stars']);
        $date    = $this->format_date($review['date']);

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
                                <div class='review_content'>".
                                    $this->process_cont($cont).
                                "</div>
                                $reply
                            </div>";
        return $review_card;
    }

    /**
     * If $reply is not null, returns a &lt;div class='reply'&gt; element. Else returns whitespace.
     *
     * @param $reply
     * @return string
     */
    protected function set_reply($reply) {
        $html = '';

        if ($reply !== null) {
            $reply = $this->process_cont($reply);
            $html .= "<div class='reply'>$reply</div>";
        }
        return $html;
    }
    
    /**
    * Formats review text to wrap each text paragraph &lt;br&gt; tag in a &lt;p&gt; tag.
    * 
    * @access   protected
    * @return   string
    */
    protected function process_cont($cont) {
        $cont = '<p>'.$cont;
        $data = preg_replace('#(?:<br\s*/?>\s*?){2,}#', '</p><p>', $cont);
        return rtrim($data, '<p></p>');
    }
    
    /**
    * Adds HTML for visible stars. The &lt;div class='star_full'&gt;&lt;/div&gt; element
    * has a star image set as the background. Used in review_card_formatter()
    * 
    * @param integer $stars Sets the number of times a loop runs to append HTML stars.
    * @access   protected
    * @return   string
    */
    protected function set_stars($stars) {
        $star_divs = '';
        for ($s = 0 ; $s < $stars ; ++$s) {
            $star_divs .= "<div class='star_full'></div>";
        }
        return $star_divs;
    }
    
    /**
    * Converts date from reviews.sql to format like: 'January 30th, 2016'
    * 
    * @param string Date string. Converted to a friendly display date.
    * @access   protected
    * @return   string
    */
    protected function format_date($date) {
        /* @ review_card formatter */
        $format = 'M j, Y';
        return date($format, strtotime($date));
    }
    
    /**
    * Returns long product name from product code.
    * 
    * @param string $prod Product ID. Used to find long product name
    * @access   protected
    * @return   string
    */
    protected function return_product($prod) {
        $prod_arr = $this->prod_array;

        $return_prod = '';
        foreach ($prod_arr as $key => $value) {
            if (strtolower($key) == strtolower($prod)) {
                $return_prod = $value;
            }
        }
        return $return_prod;
    }
    
    /**
    * Returns HTML for sidebar with review cards.
    * 
    * @access   public
    * @return   string
    */
    public function return_sidebar() {
        return $this->sidebar;
    }
}
/*
$build_sidebar = new review_sidebar('', '', 0, 10);

echo $build_sidebar->return_sidebar();
 */
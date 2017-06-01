<?php
if (!defined('RIGBY_ROOT')) {
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/abstract_widget.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');


class sidebar2 extends abstract_widget {

    protected $review_array = array();
    protected $sidebar;

    public function __construct($page, $reviews_per_page, $rating, $product_id)
    {
        parent::__construct($page, $reviews_per_page, $rating, $product_id);

        $this->review_array = $this->get_reviews($this->product_id, $this->rating, $this->start, $this->per_page);
        $this->sidebar      = $this->build_sidebar($this->review_array);
    }

    protected function get_reviews($product_id, $rating, $start, $reviews_per_page)
    {

        $query = "SELECT * FROM star_reviews ";
        $pdo = array();

        if ($product_id !== false || $rating !== false)
        {
            $query .= 'WHERE ';
        }

        if ($product_id !== false)
        {
            $query .= 'product_id = ? AND ';
            $pdo[] = $product_id;
        }
        if ($rating !== false)
        {
            $query .= 'stars = ? ';
            $pdo[] = $rating;
        }

        $query .= ' ORDER BY date desc';
        $query .= ' LIMIT ?, ?';
        $pdo[] = $start;
        $pdo[] = $reviews_per_page;

        try {
            $results = sql_pdo::run($query, $pdo)->fetchAll();
            return $results;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return array();
        }
    }

    protected function build_sidebar(array $review_array) {

        if (empty($review_array))
        {
            $error = "<div class='error'>No reviews to display!</div>";
            return "<div id='review_col'>$error</div>";
        }

        $review_cards = '';

        foreach ($review_array as $review) {
            $review_cards .= $this->review_card_formatter($review);
        }

        $review_navigation_js = $this->get_js_filepath('review_navigation');


        $review_column = "<div id='review_col'>$review_cards</div>";
        $review_column .= "<script type='text/javascript' src='$review_navigation_js'></script>";

        return $review_column;
    }

    protected function review_card_formatter(array $review) {
        $title   = $review['title'];
        $name    = $review['name'];
        $product = $this->get_product_name($review['product']);
        $cont    = $this->process_content($review['cont']);
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

    protected function get_product_name($product_id)
    {
        if (trim($product_id) == '')
        {
            return '';
        }
        $query = "SELECT product_name FROM products WHERE product_id = ?";

        try {
            return sql_pdo::run($query, [$product_id])->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return '';
        }
    }

    protected function process_content($cont) {
        $cont = '<p>'.$cont;
        $data = preg_replace('#(?:<br\s*/?>\s*?){2,}#', '</p><p>', $cont);
        return rtrim($data, '<p></p>');
    }

    protected function set_stars($stars) {
        $star_divs = '';
        for ($s = 0 ; $s < $stars ; ++$s) {
            $star_divs .= "<div class='star_full'></div>";
        }
        return $star_divs;
    }

    protected function format_date($date) {
        $format = 'M j, Y';
        return date($format, strtotime($date));
    }

    protected function set_reply($reply) {
        $html = '';

        if ($reply !== null) {

            $reply = $this->process_content($reply);
            $html .= "<div class='reply'>$reply</div>";
        }
        return $html;
    }

    function get_js_filepath($js_file_name)
    {
        $path = RIGBY_ROOT;
        $approot = substr($path,strlen($_SERVER['DOCUMENT_ROOT']));
        $url  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
//        $js_file = $_SERVER["SERVER_NAME"] . $approot . "/js/review_navigation.js";
        $js_file = $_SERVER["SERVER_NAME"] . $approot . "/js/$js_file_name.js";

        return $url . '://' . $js_file;
    }

    public function return_sidebar()
    {
        return $this->sidebar;
    }
}

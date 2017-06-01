<?php
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This abstract class is used to provide a common constructor and
 * methods to set class variables for Rigby review pagination and results.
 *
 * Constructor arguments get be whitespace. They'll either be set to a
 * default value or set by $_GET values.
 */
abstract class review_navigate_abstract
{
    protected $review_count;

    /**
     * @var int Pointer for pagination. Specifies the result page requested by Rigby user. If
     * whitespace, will be set to $_GET['page'].
     */
    protected $page;

    /** @var float The last page needed given the number of reviews and reviews to display per page.
     *             Set by review_navigate_abstract::set_reviews_per_page().
     */
    protected $last_page;

    /**
     * @var int|string Ratings Rigby is asked to display. If whitespace, will either be the
     * value of $_GET['rating'] if that's present in the URL query string. Else, set to whitespace
     * (which will be evaluated as '%' when getting results from class review_return.
     */
    protected $rating;

    /**
     * @var string The product_id the review search is for
     */
    protected $product_id;

    /**
     * @var int Sets the number of results that will be displayed per page. If whitespace, defaults
     * to = 8.
     */
    protected $reviews_per_page;

    /**
     * @param $page     int|string Navigation page requested. If whitespace, defaults to $_GET['page'] if that's set.
     * @param $rating   int|string Rating being requested. If whitespace, defaults to $_GET['rating'] if that's set.
     * @param $reviews_per_page int|string Number of results per page. If whitespace, defaults to 8.
     */
    public function __construct($page, $rating, $reviews_per_page)
    {
        $this->rating           = $this->set_rating($rating);
        $this->reviews_per_page = $this->set_reviews_per_page($reviews_per_page);
        $this->review_count     = $this->get_review_count($rating);
        $this->last_page        = $this->set_last_page($this->review_count, $this->reviews_per_page);
        $this->page             = $this->set_page($page, $this->last_page);
    }

    /**
     * If whitespace, checks if $_GET['page'] is set and returns int value for that. Else,
     * returns int value for $page. If the method is passed a $page value greater than the $last_page
     * value, output will always be equal to $last_page.
     *
     * @param $page int The page being requested.
     * @param $last_page float The last page defined by review_navigate_abstract::set_last_page()
     * @return int Will never return less than 1.
     */
    protected function set_page($page, $last_page)
    {
        $out = 1;

        if (trim($page) !== '')
        {
            $out = (int)$page;
        }

        if (isset($_GET['page']))
        {
            $out = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
        }

        /* Rigby user can never occupy a page greater than the last page. */
        if ($out > $last_page)
        {
            $out = $last_page;
        }

        if ($out < 1)
        {
            $out = 1;
        }

        return $out;
    }

    /**
     * If whitespace, checks if $_GET['rating'] is set and returns int value for that. Else,
     * returns int value for $rating.
     *
     * @param $rating int|string The rating being requested.
     * @return int|string Will never return less than 1 or more than 5.
     */
    protected function set_rating($rating)
    {
        $out = 5;
        if (trim($rating) !== '')
        {
            $out = (int)$rating;
        } else {
            $out = '';
        }
        if (isset($_GET['rating']))
        {
            $out = (int)filter_var($_GET['rating'], FILTER_SANITIZE_STRING);
        }

        switch ($out)
        {
            case '':
                break;
            default:
                if ($out < 1) {
                    $out = 1;
                }
                if ($out > 5) {
                    $out = 5;
                }
                break;
        }
        return $out;
    }

    /**
     * Sets the number of reviews that should display per page. If $review_per_page is whitespace,
     * defaults to return 8.
     *
     * @param $reviews_per_page int|string Number of reviews requested per page.
     * @return int Will never return less than 1.
     */
    protected function set_reviews_per_page($reviews_per_page)
    {
        $out = 8;
        if (trim($reviews_per_page !== ''))
        {
            $out = (int)$reviews_per_page;
        }
        if ($out < 1)
        {
            $out = 1;
        }
        return $out;
    }

    /**
     * Gets the total number of reviews that aren't hidden and fit the criteria passed to the function.
     *
     * @param $rating int The review rating being requested. Will always be 1-5.
     * @return string int The total count found by the PDO query.
     */
    protected function get_review_count($rating)
    {
        if (trim($rating) == '') {
            $rating = '%';
        }
        $query = "SELECT COUNT(*) FROM star_reviews WHERE hidden !=1 AND stars LIKE ?;";
        $rev_count = sql_pdo::run($query, [$rating])->fetchColumn();
        return $rev_count;
    }

    /**
     * Gets the total number of pages needed to display all reviews given the $review_count and
     * the $reviews_per_page arguments. Will always return a minimum value of 1.
     *
     * @param $review_count int Set by review_navigate_abstract::get_review_count()
     * @param $reviews_per_page int The max number of reviews to display per page.
     * @return float|int Returns minimum value of 1, or the number of pages necessary to display
     *                   all review data.
     */
    protected function set_last_page($review_count, $reviews_per_page)
    {
        if ($review_count <= 0)
        {
            return 1;
        } else {
            return ceil($review_count / $reviews_per_page);
        }

    }
}
<?php
require_once('build_reviews_table.php');
require_once('search_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This class is responsible for collecting $_GET data and passing it to the build_review_table class.
 *
 * The class is extended from search_abstract, so it shares a parent constructor with search_pagination (which is
 * extended from the same parent class).
 *
 * Search_reviews and search_pagination are used in tandem. The pagination bar acts as a method of traversing results
 * found by the search_reviews class.
 *
 * Used in:
 * - ../reviews.php
 */
class search_reviews extends search_abstract
{
    /**
     * @var string Holds HTML for the review table.
     */
    protected $review_table;

    public function __construct()
    {
        // Parent constructor sets class variables from $_GET values.
        parent::__construct();

        // Build HTML for the review table.
        $this->review_table = $this->build_table();
    }

    /**
     * Collects class variables set in the parent constructor and passes them to the build_review_table.
     *
     * @return string
     */
    protected function build_table()
    {
        $title  = $this->title;
        $name   = $this->name;
        $email  = $this->email;
        $ip     = $this->ip;

        $page   = $this->page;
        $page_p = $this->page_p;

        $date_range     = $this->date_range;
        $date_single    = $this->date_single;
        $date_start     = $this->date_start;
        $date_end       = $this->date_end;
        $star_array     = $this->stars_array;


        $build_table = new build_reviews_table($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array);
        return $build_table->return_table();
    }

    /**
     * Public access for the HTML review table.
     *
     * @return string HTML for the review table.
     */
    public function return_table()
    {
        return $this->review_table;
    }
}

<?php

require_once('build_pagination.php');
require_once('search_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This class is responsible for collecting $_GET data and passing it to the build_pagination class.
 *
 * The class is extended from search_abstract, so it shares a parent constructor with search_reviews.
 *
 * Search_reviews and search_pagination are used in tandem. The pagination bar acts as a method of traversing results
 * found by the search_reviews class.
 *
 * Used in:
 * - ../reviews.php
 */
class search_pagination extends search_abstract {

    /**
     * @var string Holds html for the pagination bar.
     */
    protected $pagination_bar;

    public function __construct(){

        // Call the parent constructor. This sets collects $_GET values and sets them as protected class variables.
        parent::__construct();

        // Set the pagination bar HTML.
        $this->pagination_bar = $this->build_pagination();
    }

    /**
     * Collect variables set in the parent constructor and feed those variables to the pagination2 class.
     *
     */
    protected function build_pagination() {
        $title       = $this->title;
        $name        = $this->name;
        $email       = $this->email;
        $ip          = $this->ip;
        $page        = $this->page;
        $page_p      = $this->page_p;
        $date_r      = $this->date_range;
        $date_single = $this->date_single;
        $date_start  = $this->date_start;
        $date_end    = $this->date_end;
        $star_array  = $this->stars_array;
        
        $build_pagination = new build_pagination($title, $name, $email, $ip, $page, $page_p, $date_r, $date_single, $date_start, $date_end, $star_array);
        return $build_pagination->return_pagination();
    }

    /**
     * Public access for the HTML pagination bar.
     *
     * @return string
     */
    public function return_pagination() {
        return $this->pagination_bar;
    }
}

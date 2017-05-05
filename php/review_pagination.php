<?php

require_once('sql_pdo/sql_define.php');
require_once('sql_pdo/sql_pdo.php');
require_once('trait_check_arg.php');

/**
 * review_pagination
 *
 * @package    Rigby
 * @author     Gabriel Mioni <gabriel@gabrielmioni.com>
 */

class review_pagination {
    
    /**
    * - review_pagination builds an html <ul> div that's used to navigate
    *   between review result pages. 
    * 
    *   This class will likely be removed. Another class found in 
    *   review_admin/pagination2.php.
    *
    *   Used in:
    *   - star-reviews/index.php
    *
    * @param integer $page          - Pointer for the clicked page.
    * @param integer $curent_page   - Pointer for the present page.
    * @param integer $revs_per_page - Sets how many reviews are on each page.
    * @param integer $star_rev      - Sets whether the prepared statement returns 
    *                                 reviews with a specific rating (1-5) or all reviews.
    *
    * @return string [ $pagination_bar, which holds html for the pagination bar ]
    */
    
    // Found in trait_check_arg. Trait contains the check_arg() method.
    use check_arg;
    
    protected $page;
    protected $curent_page;
    protected $revs_per_page;
    protected $star_rev;
    
    // Set in get_rev_count(). Holds total number of reviews.
    protected $rev_count;

    // Set in build_pagination(). Holds total pages needed in pagination bar.
    protected $pages_needed;
    
    // Holds html for the pagination bar. This is the variable returned by the class.
    protected $pagination_bar;
    
    // Holds integer with total count of pages. Used to display total to user.
    protected $pagination_count;

    public function __construct($page, $curent_page, $revs_per_page, $star_rev) {
        $this->curent_page   = $curent_page;
        $this->page          = $page;
        $this->revs_per_page = $revs_per_page;
        $this->star_rev      = $this->check_arg($star_rev, '%');
        $this->rev_count     = $this->get_rev_count();
        
        $this->start_pagination_build();        
    }

    /**
    * start_pagination_build
    *
    * Sets $this->pages_needed and starts build_pagination($pages_needed)
    * 
    * @param integer $revs_per_page
    * @param integer $rev_count
    * 
    * @return void
    */
    protected function start_pagination_build() {
        $revs_per    = $this->revs_per_page;
        $rev_count   = $this->rev_count;
        
        $pages_needed = ceil($rev_count / $revs_per);
        $this->pages_needed = $pages_needed;
        
        $this->build_pagination($pages_needed);
    }
    
    /**
    * build_pagination
    *
    * Sets $this->pagination_bar with HTML for the pagination bar.
    * Sets $this->pagination_count with total page count to be displayed to the user.
    * 
    * @param integer $pages_needed
    * 
    * @return void
    */
    protected function build_pagination($pages_needed) {
        $current_page = $this->curent_page;
        $set_page     = $this->page;
        $rating       = $this->star_rev;
        
        $back    = $current_page -1;
        $forward = $current_page +1;
        
        
        $url = "http://gabrielmioni.com/projects/star-reviews/index.php?page=";
        
        if ($rating !== '%') {
            $rating_set = "&rating=$rating";
        } else {
            $rating_set = '';
        }
        
        $end = ceil($current_page / 10) * 10;
        $start = ($end - 9);
                
        if ($pages_needed < $end) {
            $set_end = $pages_needed;
        } else {
            $set_end = $end;
        }
        $bar =  "<ul>";
        
        /* Set left arrows */
        switch ($current_page) {
            case $current_page == 1:
                $bar .= "<li class = 'nav_faded nav left_all'><i class='fa fa-angle-double-left' aria-hidden='true'></i></li>";
                $bar .= "<li class = 'nav_faded nav left_one'><i class='fa fa-angle-left' aria-hidden='true'></i></li>";
                break;
            default:
                $bar .= "<li class = 'nav left_all'><a href='$url".'1'."$rating_set'><i class='fa fa-angle-double-left' aria-hidden='true'></i></a></li>";
                $bar .= "<li class = 'nav left_one'><a href='$url".$back."$rating_set'><i class='fa fa-angle-left' aria-hidden='true'></i></a></li>";
                break;
        }
        /* Set page view*/
        for ($p = $start ; $p <= $set_end ; ++$p) {
            switch ($p) {
                case $p == $set_page:
                    $bar .= "<li class='selected'>$p</li>";
                    break;
                default:
                    $bar .= "<li><a href='$url".$p."$rating_set'>$p</a></li>";
                    break;
            }
        }
        /* Set right arrows */
        switch ($current_page) {
          case $current_page == $pages_needed:
            $bar .= "<li class = 'nav_faded nav right_one'><i class='fa fa-angle-right' aria-hidden='true'></i></li>";
            $bar .= "<li class = 'nav_faded nav right_all'><i class='fa fa-angle-double-right' aria-hidden='true'></i></li>";
            break;
          default:
            $bar .= "<li class = 'nav right_one'><a href='$url".$forward."$rating_set'><i class='fa fa-angle-right' aria-hidden='true'></i></a></li>";
            $bar .= "<li class = 'nav right_all'><a href='$url"."$pages_needed"."$rating_set'><i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
            break;
        }
        $bar .= "<span class='hidden'>$pages_needed</span>";
        $bar .=     '</ul>';
        $pagination_count = "<div class='page_view_count'>
                                <i><span class='min'>$current_page</span> / <span class='max'>$pages_needed</span></i>
                            </div>";
        $this->pagination_bar = $bar;
        $this->pagination_count = $pagination_count;
    }
    
    /**
    * get_rev_count
    *
    * Returns total review count. Used to set $this->rev_count.
    * 
    * @param string $star_rev
    * 
    * @return integer
    */
    protected function get_rev_count() {
        $star_rev = $this->star_rev;
        $query = "SELECT COUNT(*) FROM star_reviews WHERE hidden !=1 AND stars LIKE ?;";
        $rev_count = sql_pdo::run($query, [$star_rev])->fetchColumn();
        return $rev_count;
    }
    
    /**
    * return_pagination_bar
    *
    * Returns the HTML pagination bar
    * 
    * @return string
    */
    public function return_pagination_bar() {
        return $this->pagination_bar;
    }
    
    /**
    * return_rev_count
    *
    * Returns total review count
    * 
    * @return integer
    */
    public function return_rev_count() {
        return $this->rev_count;
    }
    
    /**
    * return_pages_needed
    *
    * Deprecated. Returns total pages needed
    * 
    * @return integer
    */
    public function return_pages_needed() {
        return $this->pages_needed;
    }
    
    /**
    * return_pagination_count
    *
    * Returns total pages.
    * 
    * @return integer
    */
    public function return_pagination_count() {
        return $this->pagination_count;
    }
}


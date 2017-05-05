<?php
require_once('review_navigate_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class paginator
 *
 * Builds HTML for a pagination bar used to navigate reviews.
 *
 * The pagination bar will display a number of buttons equal to the value of paginator::buttons_needed. Each button
 * represents a page of review results.
 *
 * The pagination bar is broken into 'chunks.' Example: paginator::buttons_per_bar are set to 10. If there are 250
 * reviews and each page displays 10 reviews, 25 buttons would be needed to represent each page. Chunk 1 would have
 * pages 1-10, Chunk 2 would have pages 11-20, and chunk 3 would have pages 21-25.
 *
 * Navigation buttons are used to traverse chunks, going both forward and backward. If backward or forward options are
 * unavailable because the Rigby use is either at the beginning or end of the pagination bar already, the appropriate
 * navigation arrows will be greyed out and unclickable.
 *
 * HTML is only built for the current pagination chunk being viewed.
 *
 * Parent class review_navigate_abstract is also extended by the class sidebar. Both paginator and sidebar
 * share a common __constructor so they will both have the same $_GET data for $page and $rating.
 */
class paginator extends review_navigate_abstract
{
    /** @var int The number of buttons that should be in the pagination bar. Default is 10. */
    protected $buttons_per_bar;

    /** @var array Holds array data that represents the total number of pagination buttons needed */
    protected $pagination_chunks = array();

    /** @var string HTML for the current pagination chunk being displayed. */
    protected $pagination_html;

    protected $ajax_url;

    /**
     * @param int|string $page  The page of review results being displayed. If whitespace, value will be
     *                          $_GET['page']. If that value is unset, default will be 1. Set in parent __constructor.
     * @param int|string $rating    The rating value being requested for display. If whitespace, value will be
     *                              $_GET['rating']. If whitespace, value will be evaluated as '%'
     *                              in paginator::get_review_count(). Set in parent __constructor.
     * @param int|string $reviews_per_page  Number of reviews displayed in each review result page. If whitespace,
     *                                      default will be 8. Set in parent __constructor.
     * @param $buttons_per_bar int  Number of buttons set to display in the pagination bar.
     * @param $ajax_url string  Default is whitespace.
     */
    public function __construct($page, $rating, $reviews_per_page, $buttons_per_bar, $ajax_url = '')
    {
        parent::__construct($page, $rating, $reviews_per_page);

        $this->ajax_url          = $ajax_url;
        $this->buttons_per_bar   = $buttons_per_bar;
        $this->pagination_chunks = $this->set_pagination_chunks($this->last_page, $this->buttons_per_bar);
        $this->pagination_html   = $this->build_pagination_bar($this->page, $this->rating, $this->last_page, $this->pagination_chunks, $this->ajax_url);

    }

    /**
     * Returns a multi-dimensional array representing all needed pagination bars.
     *
     * Creates an array with elements representing each pagination button that's needed, then splits that array into
     * 'chunks' that holds a max element count equal to the $buttons_per_bar argument.
     *
     * @param $buttons_needed int Total number of buttons for all pages.
     * @param $buttons_per_bar int The max number of buttons the pagination bar should display.
     * @return array    Multi-dimensional array. Each array element represents a pagination bar. Each child element
     *                  represents a button in the pagination bar.
     */
    protected function set_pagination_chunks($buttons_needed, $buttons_per_bar)
    {
        $super_chunk = array();

        for ($c = 0 ; $c < $buttons_needed ; ++$c)
        {
            $super_chunk[] = ($c+1);
        }

        $chunks = array_chunk($super_chunk, $buttons_per_bar);

        return $chunks;
    }

    /**
     * Finds the pagination array chunk the Rigby user is viewing.
     *
     * Checks each pagination chunk to see if the $page argument is present in the array elements of
     * the array. If the method finds the array element where $page is, it sets $pointer to the value of the
     * array key and returns that $pointer value.
     *
     * @param $page int The current page the Rigby user is viewing.
     * @param array $pagination_chunk   Multi-dimensional array produced by sidebar::set_pagination_chunks()
     * @return int|null If the $page value is not found in any $pagination_chunk child elements, returns NULL. Else,
     *                  returns the key for the array that has the $page element.
     */
    protected function set_chunk_pointer($page, array $pagination_chunk)
    {
        $pointer = NULL;
        foreach ($pagination_chunk as $key => $chunk)
        {
            $in_array = in_array($page, $chunk);
            if ($in_array == TRUE)
            {
                $pointer = $key;
                break;
            }
        }
        return $pointer;
    }

    /**
     * Builds HTML for the pagination bar, including href values for each link in each button.
     *
     * @param $page     int The page being requested by the Rigby user.
     * @param $rating   int The review value being requested. Used to build URL query string.
     * @param $last_page float The last page defined in review_navigate_abstract::set_last_page().
     * @param array $pagination_chunk All array elements from sidebar::set_pagination_chunks().
     * @uses sidebar::build_left_arrows()
     * @uses sidebar::build_right_arrows()
     * @uses sidebar::get_last_button()
     * @uses sidebar::get_chunk_pointer()
     * @uses sidebar::set_url_rating()
     * @return string HTML for the pagination bar.
     */
    protected function build_pagination_bar($page, $rating, $last_page, array $pagination_chunk, $ajax_url)
    {
        /** @var int $chunk_pointer The key for the $pagination_chunk child element the Rigby user occupies. */
        $chunk_pointer = $this->set_chunk_pointer($page, $pagination_chunk);

        /** @var $url string URL that's used as base for pagination button links. Does not include query string. */

        if ($ajax_url !== '')
        {
            $url = $ajax_url;
        } else {
            $url = strtok($_SERVER["REQUEST_URI"],'?');
        }

        /** @var string $set_rating If rating is set, will be appropraite query string to append to URL. Else, whitespace. */
        $set_rating = $this->set_url_rating($rating);

        /* Find the parent array in $pagination_chunk that should be used to build the pagination bar. */
        if (isset($pagination_chunk[$chunk_pointer]))
        {
            $current_chunk = $pagination_chunk[$chunk_pointer];
        } else {
             $current_chunk = array();
        }

        /* Start building HTML for pagination bar */
        $pagination_html = '<div id="pagination_bar"><ul>';

        /* Set left navigation arrows HTML */
        $pagination_html .= $this->build_left_arrows($page, $url, $set_rating);

        /* Set all pagination buttons */
        foreach ($current_chunk as $page_id)
        {
            if ($page_id == $page)
            {
                $pagination_html .= "<li class='selected'>$page_id</li>";
            } else {
                $set_url    = "$url?page=$page_id" . $set_rating;
                $pagination_html .= "<li><a href='$set_url'>$page_id</a></li>";
            }
        }

        /* Set right navigation arrows HTML */
        $pagination_html .= $this->build_right_arrows($page, $last_page, $url, $set_rating);

        /* Close the <ul> and <div id='pagination_bar'> elements */
        $pagination_html .= '</ul></div>';

        return $pagination_html;
    }

    /**
     * Returns HTML for two left navigation buttons. The 'left_all' takes the Rigby user back to the first page.
     * The 'left_one' button takes the Rigby user back a single page. If the Rigby user is already at the beginning
     * of the pagination bar ($page == 1), left navigation buttons will be greyed out and unclickable.
     *
     * @param $page       int Current page being viewed by the Rigby user.
     * @param $url        string URL used as the base for button links.
     * @param $set_rating string The query string for 'rating' value set by paginator::set_url_rating. Can be whitespace.
     * @return string HTML for both left navigation buttons.
     */
    protected function build_left_arrows($page, $url, $set_rating)
    {
        $left_button_html = '';

        /* If $page is one, return unclickable greyed out navigation buttons. */
        if ($page == 1)
        {
            $left_button_html .= "<li class = 'nav_faded nav left_all'><i class='fa fa-angle-double-left' aria-hidden='true'></i></li>";
            $left_button_html .= "<li class = 'nav_faded nav left_one'><i class='fa fa-angle-left' aria-hidden='true'></i></li>";
        } else {
            $back_one = $page -1;
            $back_one_url  = "$url?page=$back_one" . $set_rating;
            $back_all_url  = "$url?page=1" . $set_rating;

            $left_button_html .= "<li class = 'nav left_all'><a href='$back_all_url'><i class='fa fa-angle-double-left' aria-hidden='true'></i></a></li>";
            $left_button_html .= "<li class = 'nav left_one'><a href='$back_one_url'><i class='fa fa-angle-left' aria-hidden='true'></i></a></li>";
        }
        return $left_button_html;
    }

    /**
     *
     *
     * @param $page int
     * @param $last_button
     * @param $url
     * @param $set_rating
     * @return string
     */
    protected function build_right_arrows($page, $last_button, $url, $set_rating)
    {
        $right_button_html = '';

        /* If $page is greater or equal to $last_button, return unclickable greyed out navigation buttons. */
        if ($page >= $last_button)
        {
            $right_button_html .= "<li class = 'nav_faded nav right_one'><i class='fa fa-angle-right' aria-hidden='true'></i></li>";
            $right_button_html .= "<li class = 'nav_faded nav right_all'><i class='fa fa-angle-double-right' aria-hidden='true'></i></li>";
        } else {
            $forward_one = $page +1;
            $forward_one_url  = "$url?page=$forward_one" . $set_rating;
            $forward_all_url  = "$url?page=$last_button" . $set_rating;

            $right_button_html .= "<li class = 'nav right_one'><a href='$forward_one_url'><i class='fa fa-angle-right' aria-hidden='true'></i></a></li>";
            $right_button_html .= "<li class = 'nav right_all'><a href='$forward_all_url'><i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
        }
        return $right_button_html;
    }

    protected function set_url_rating($rating)
    {
        if ($rating != '')
        {
            $out = "&rating=$rating";
        } else {
            $out = '';
        }
        return $out;
    }

    public function get_pagination_bar()
    {
        return $this->pagination_html;
    }
}

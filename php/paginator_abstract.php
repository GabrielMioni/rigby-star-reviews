<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Used to build HMTL for pagination bars, which include 'buttons' with links that allow a user to navigate results.
 *
 * In concrete instances, the number of results are set by the abstract method paginator_abstract::set_result_count().
 * Usually this would be an SQL query that gets a total count of results that need to be navagable.
 *
 * The abstract method paginator_abstract::set_url_query_string() builds a common query string used for each button.
 * This is used to pass $_GET values to script generating viewable results.
 */
abstract class paginator_abstract
{
    /** @var int The current page being viewed */
    protected $page;

    /** @var int Holds the number of results displayed per page */
    protected $results_per_page;

    /** @var int The number of buttons that should be in the pagination bar. Default is 10. */
    protected $buttons_per_bar;

    /** @var string Holds query string data if a concrete paginator object is created using Ajax */
    protected $ajax_url;

    /** @var string Holds common query string data that will be used for all links/buttons in the pagination bar */
    protected $query_string;

    /** @var int The number of total results that can be displayed across all pages. */
    protected $result_count;

    /** @var float|int The last page necessary to display all results. */
    protected $last_page;

    /** @var array Holds array data that represents the total number of pagination buttons needed */
    protected $pagination_chunks = array();

    /** @var string HTML for the current pagination chunk being displayed. */
    protected $pagination_html;

    /**
     * @param int|string $page  The page of review results being displayed. If whitespace, value will be
     *                          $_GET['page']. If that value is unset, default will be 1. Set in parent __constructor.
     * @param int|string $results_per_page  Number of reviews displayed in each review result page. If whitespace,
     *                                      default will be 8. Set in parent __constructor.
     * @param $buttons_per_bar int  Number of buttons set to display in the pagination bar.
     * @param $ajax_url string  Default is whitespace.
     */
    public function __construct($page, $results_per_page, $buttons_per_bar, $ajax_url = '')
    {
        $this->page              = $this->set_page($page, $this->last_page);
        $this->results_per_page  = $this->set_results_per_page($results_per_page);
        $this->buttons_per_bar   = $buttons_per_bar;
        $this->ajax_url          = $ajax_url;
        $this->query_string      = $this->set_url_query_string();

        $this->result_count      = $this->set_result_count();
        $this->last_page         = $this->set_last_page($this->result_count, $this->results_per_page);

        $this->pagination_chunks = $this->set_pagination_chunks($this->last_page, $this->buttons_per_bar);

        $this->pagination_html   = $this->build_pagination_bar($this->page, $this->last_page, $this->pagination_chunks, $this->ajax_url, $this->query_string);
    }

    /**
     * This will be a query to get a count for the number of records.
     *
     * @return int
     */
    protected abstract function set_result_count();

    /**
     * @return string Query string.
     */
    protected abstract function set_url_query_string();

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

        if (isset($_GET['page']))
        {
            $out = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
            return $out;
        }

        if (trim($page) !== '')
        {
            $out = (int)$page;
            return $out;
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
     * Sets the number of reviews that should display per page. If $review_per_page is whitespace,
     * defaults to return 8.
     *
     * @param $reviews_per_page int|string Number of reviews requested per page.
     * @return int Will never return less than 1.
     */
    protected function set_results_per_page($reviews_per_page)
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
     * @param $page int     The page being requested by the Rigby user.
     * @param $last_page float  The last page defined in review_navigate_abstract::set_last_page().
     * @param array $pagination_chunk   All array elements from sidebar::set_pagination_chunks().
     * @param $ajax_url string  Holds query string data if the concrete object is being created for an Ajax call.
     * @param $query_string string  Holds query string data set by paginator_abstract::set_url_query_string().
     *
     * @return string HTML for the pagination bar.
     */
    protected function build_pagination_bar($page, $last_page, array $pagination_chunk, $ajax_url, $query_string)
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
        $pagination_html .= $this->build_left_arrows($page, $url, $query_string);

        /* Set all pagination buttons */
        foreach ($current_chunk as $page_id)
        {
            if ($page_id == $page)
            {
                $pagination_html .= "<li class='selected'>$page_id</li>";
            } else {
                $set_url    = "$url?page=$page_id" . $query_string;
                $pagination_html .= "<li><a href='$set_url'>$page_id</a></li>";
            }
        }

        /* Set right navigation arrows HTML */
        $pagination_html .= $this->build_right_arrows($page, $last_page, $url, $query_string);

        /* Close the <ul> and <div id='pagination_bar'> elements */
        $pagination_html .= '</ul></div>';

        return $pagination_html;
    }

    /**
     * Returns HTML for two left navigation buttons. The 'left_all' takes the user back to the first page.
     * The 'left_one' button takes the Rigby user back a single page. If the Rigby user is already at the beginning
     * of the pagination bar ($page == 1), left navigation buttons will be greyed out and unclickable.
     *
     * @param $page       int Current page being viewed by the Rigby user.
     * @param $url        string URL used as the base for button links.
     * @param $query_string string The query string set by paginator::set_url_rating. Can be whitespace.
     * @return string HTML for both left navigation buttons.
     */
    protected function build_left_arrows($page, $url, $query_string)
    {
        $left_button_html = '';

        /* If $page is one, return un-clickable greyed out navigation buttons. */
        if ($page == 1)
        {
            $left_button_html .= "<li class = 'nav_faded nav left_all'><i class='fa fa-angle-double-left' aria-hidden='true'></i></li>";
            $left_button_html .= "<li class = 'nav_faded nav left_one'><i class='fa fa-angle-left' aria-hidden='true'></i></li>";
        } else {
            $back_one = $page -1;
            $back_one_url  = "$url?page=$back_one" . $query_string;
            $back_all_url  = "$url?page=1" . $query_string;

            $left_button_html .= "<li class = 'nav left_all'><a href='$back_all_url'><i class='fa fa-angle-double-left' aria-hidden='true'></i></a></li>";
            $left_button_html .= "<li class = 'nav left_one'><a href='$back_one_url'><i class='fa fa-angle-left' aria-hidden='true'></i></a></li>";
        }
        return $left_button_html;
    }

    /**
     * Returns HTML for two right navigation buttons. The '.right_all' takes the user all the way to the last page. The '.right_one' button
     * takes the user to the next result page.
     *
     * @param $page         int Current page being viewed by the Rigby user.
     * @param $last_button  int The last page necessary to display all results.
     * @param $url          string URL used as the base for button links.
     * @param $query_string string The query set by paginator::set_url_rating. Can be whitespace.
     * @return string HTML for both right navigation buttons.
     */
    protected function build_right_arrows($page, $last_button, $url, $query_string)
    {
        $right_button_html = '';

        /* If $page is greater or equal to $last_button, return unclickable greyed out navigation buttons. */
        if ($page >= $last_button)
        {
            $right_button_html .= "<li class = 'nav_faded nav right_one'><i class='fa fa-angle-right' aria-hidden='true'></i></li>";
            $right_button_html .= "<li class = 'nav_faded nav right_all'><i class='fa fa-angle-double-right' aria-hidden='true'></i></li>";
        } else {
            $forward_one = $page +1;
            $forward_one_url  = "$url?page=$forward_one" . $query_string;
            $forward_all_url  = "$url?page=$last_button" . $query_string;

            $right_button_html .= "<li class = 'nav right_one'><a href='$forward_one_url'><i class='fa fa-angle-right' aria-hidden='true'></i></a></li>";
            $right_button_html .= "<li class = 'nav right_all'><a href='$forward_all_url'><i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
        }
        return $right_button_html;
    }


    public function get_pagination_bar()
    {
        return $this->pagination_html;
    }
}

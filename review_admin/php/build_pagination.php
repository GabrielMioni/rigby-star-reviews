<?php
require_once('build_abstract.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Builds a pagination bar based on values passed to the constructor.
 *
 * The class finds how many reviews fit the search criteria passed to the constructor and then builds
 * an HTML pagination bar used to traverse the search results.
 *
 * Extended from abstract class abst_review_select.
 *
 */
class build_pagination extends build_abstract {

    /**
     * @var array Holds HTML elements for the page buttons.
     */
    protected $page_buttons;

    /**
     * @var array Holds HTML elements for the left buttons.
     */
    protected $left_buttons;

    /**
     * @var array Holds HTML elements for the right buttons.
     */
    protected $right_buttons;

    /**
     * @var string Holds HTML for the pagination bar.
     */
    protected $pagination_bar;

    /**
     * Set by $this->determine_current_chunk(), which calls $this->get_max_page();
     *
     * @var integer Holds highest page label for the pagination bar.
     */
    protected $max_page;

    public function __construct($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array) {
        parent::__construct($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array);

        /* ********************************************
         * Get information to build the pagination bar
         * - $pages_per:      How many reviews to display per page.
         * - $requested_page: Page selected by Rigby user.
         * - $review_count:   Total number of reviews.
         * ********************************************/
        $pages_per      = $this->search_array['page_p'];
        $requested_page = $this->search_array['page'];
        $review_count   = $this->get_review_count();

        /* ********************************************
         * Find the currently viewed pagination bar. Set page label values.
         * ********************************************/
        $page_label_array = $this->set_page_label_array($pages_per, $requested_page, $review_count);


        /* ********************************************
         * Build HTML for the pagination bar
         * ********************************************/
        $this->left_buttons  = $this->build_left_buttons($requested_page);
        $this->page_buttons  = $this->build_page_buttons($requested_page, $page_label_array);
        $this->right_buttons = $this->build_right_buttons($requested_page, $this->max_page, $page_label_array);

        $this->pagination_bar = $this->build_pagination_bar($this->left_buttons,
                                                            $this->page_buttons,
                                                            $this->right_buttons);
    }

    /**
     * Returns count from reviews.sql that fit the criteria set in the $this->pdo_array.
     *
     * Calls $this->build_review_select() from abstract class abst_review_select.
     *
     * @return integer
     */
    protected function get_review_count() {
        $build_review_count = $this->build_review_select("COUNT(*)", 0);
        return $build_review_count[0]['COUNT(*)'];
//        $this->review_count = $build_review_count[0]['COUNT(*)'];
    }

    /**
     * Returns an array that represents the current 'view' of the HTML
     * pagination bar.
     *
     * Pagination bars are represented by arrays grouped in 10. The method
     * calculates each pagination bar necessary and then determines which one
     * to display by calling $this->determine_current_chunk().
     *
     * @param $pages_per integer Reviews to display per page.
     * @param $review_count integer Total review count.
     * @param $page_requested integer The page the Rigby user selected.
     * @return array Array representing currently viewed pagination bar or an empty array.
     */
    protected function set_page_label_array($pages_per, $page_requested, $review_count) {
        // Init. holding array for page starts.
        $page_labels = array();

        // Determine the total number of pages needed.
        $pages_needed = ceil($review_count / $pages_per);

        // Create an array with a length == $pages_needed
        for ($p = 1 ; $p <= $pages_needed ; ++$p) {
            $page_labels[] = $p;
        }

        // Chunk $page_labels into groups of 10.
        $chunks = array_chunk($page_labels, 10, false);

        // Determine and return the chunk the Rigby user currently occupies.
        if (!empty($chunks)) {
            $return = $this->determine_current_chunk($chunks, $page_requested);
        } else {
            $return = array();
        }
        return $return;
    }

    /**
     * Finds and returns the current chunk that represents the array for the current
     * pagination bar.
     *
     * Sets $this->highest_page with the highest page from $chunk.
     *
     * Used in:
     * - $this->set_page_starts_array()
     *
     * @param array $chunk
     * @param $page_requested
     * @return mixed
     */
    protected function determine_current_chunk(array $chunk, $page_requested) {

        /* Initialize the key used to refer to the currently active $chunk. Start at 0.
         * This will be updated when the method finds the $chunk array that the  Rigby
         * user is currently viewing. */
        $group_key = 0;

        // Find the highest page in the $chunk array. This will be used in $this->build_page_arrows().
        $this->max_page = $this->get_max_page($chunk);

        // Examine each $chunk array and determine which element to return.
        foreach ($chunk as $key => $page_group) {
            $min_page = $page_group[0];
            $max_page = $page_group[count($page_group)-1];

            if ($page_requested >= $min_page && $page_requested <= $max_page) {
                $group_key = $key;
                break;
            }
        }
        $current_chunk = $chunk[$group_key];
        return $current_chunk;
    }

    /**
     * Examine the $chunk array and return the last element in the last
     * containing parent array. This will be the
     *
     * Used in $this->determine_current_chunk()
     *
     * @param array $chunk
     * @return integer
     */
    protected function get_max_page(array $chunk) {
        if (!empty($chunk)) {
            // Count total parent arrays in $chunk.
            $chunk_count = count($chunk);

            // Find the key for the last parent array in $chunk
            if ($chunk_count == 0) {
                $chunk_key = 0;
            } else {
                $chunk_key = $chunk_count-1;
            }

            // Return the last element in the last parent array in $chunk.
            $last_page = end($chunk[$chunk_key]);
            return $last_page;
        }
        else {
            return 0;
        }
    }

    /**
     * Creates page 'buttons' for the pagination bar.
     *
     * @param $page_requested integer The page that's been chosen by the Rigby user.
     * @param $page_labels array Page button labels/values for the currently viewed pagination bar.
     * @return array Array of &lt;a&gt; elements.
     */
    protected function build_page_buttons($page_requested, array $page_labels) {
        $li_array = array();

        foreach ($page_labels as $page) {
            // Set the currently selected 'Page Button' to display as selected.
            if ($page == $page_requested) {
                $li_array[] = "<li class='selected'>$page</li>";
            } else {
                // Set URL for all other 'Page Buttons'
                $li_url  = $this->build_url($page);
                $li_array[] = "<li><a href='$li_url'>$page</a></li>";
            }
        }
        return $li_array;
    }

    /**
     * Creates Left/Backward buttons for the pagination bar.
     *
     * @param $page_requested The page that's been chosen by the Rigby user.
     * @return array Array of Left/Backward buttons.
     */
    protected function build_left_buttons($page_requested) {
        $left_buttons = array();
        if ($page_requested == 1) {
            $left_buttons[] = "<li class='nav left_all nav_faded'><i class='fa fa-angle-double-left' aria-hidden='true'></i></li>";
            $left_buttons[] = "<li class='nav left_one nav_faded'><i class='fa fa-angle-left' aria-hidden='true'></i></li>";
        } else {
            $arrow_start_url = $this->build_url(1);
            $arrow_back_url = $this->build_url($page_requested-1);

            $left_buttons[] = "<li class='nav left_all'><a href='$arrow_start_url'><i class='fa fa-angle-double-left' aria-hidden='true'></i></a></li>";
            $left_buttons[] = "<li class='nav left_one'><a href='$arrow_back_url'><i class='fa fa-angle-left' aria-hidden='true'></i></a></li>";
        }
        return $left_buttons;
    }

    /**
     * Creates Right/Forward buttons for the pagination bar.
     *
     * @param $page_requested integer The page that's been chosen by the Rigby user.
     * @param $max_page       integer The highest page in the pagination bar.
     * @param $page_labels    array   Labels/Values for the pagination buttons.
     * @return array    array of Right/Forward buttons
     */
    protected function build_right_buttons($page_requested, $max_page, array $page_labels) {
        $right_buttons = array();

        $li_empty = empty($page_labels) ? true : false;

        /* If $page_labels is empty, or the Rigby user is currently on the last page, set the
         * Right Arrows to be faded out. Else, set Right Arrows with links. */
        if ($page_requested == $max_page || $li_empty === true) {
            $right_buttons[] = "<li class='nav right_one nav_faded'><i class='fa fa-angle-right' aria-hidden='true'></i></li>";
            $right_buttons[] = "<li class='nav right_all nav_faded'><i class='fa fa-angle-double-right' aria-hidden='true'></i></li>";
        } else {
            $arrow_end_url     = $this->build_url($max_page);
            $arrow_forward_url = $this->build_url($page_requested+1);

            $right_buttons[] = "<li class='nav right_one'><a href='$arrow_forward_url'><i class='fa fa-angle-right' aria-hidden='true'></i></a></li>";
            $right_buttons[] = "<li class='nav right_all'><a href='$arrow_end_url'><i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
        }
        return $right_buttons;
    }

    /**
     * Returns a URL to be used as a link for pagination buttons. Includes $_GET values
     * for that will be used to return reviews based on search criteria.
     *
     * Used in:
     * - $this->build_left_buttons()
     * - $this->build_right_buttons()
     * - $this->build_page_buttons();
     *
     * @param $page integer The page label for the button being created.
     * @return string The URL that will be set in the page button's &lt;li&gt; element
     */
    protected function build_url($page){
        $search_array = $this->search_array;

        $orig_url = $_SERVER['REQUEST_URI'];
        $expl = explode('?', $orig_url);
        $url = "$expl[0]?";

        foreach ($search_array as $key => $search) {
            if ($key == 'page') {
                continue;
            }
            if ($key == 'page_p') {
                $url .= "&page_p=$search";
                continue;
            }
            if (is_array($search)) {
                foreach ($search as $key => $star) {
                    $url .= "&star-$star=on";
                }
                continue;
            }
            switch ($search) {
                case '':
                    break;
                case '%':
                    break;
                default:
                    $url .= "&$key"."_search=$search";
                    break;
            }
        }
        return "$url&page=$page";
    }

    /**
     * Puts together the three parts of the pagination bar:
     * - Left direction buttons.
     * - Page buttons
     * - Right direction buttons
     *
     * @param $left_buttons array HTML for left buttons
     * @param $page_buttons array HTML for page buttons
     * @param $right_buttons array HTML for right buttons
     * @return string $bar HTML for the pagination bar
     */
    protected function build_pagination_bar(array $left_buttons, array $page_buttons, array $right_buttons) {
        $bar = "<div id='pagination_bar'><ul>";
        foreach ($left_buttons as $arrow_l) {
            $bar .= $arrow_l;
        }
        foreach ($page_buttons as $li) {
            $bar .= $li;
        }
        foreach ($right_buttons as $arrow_r) {
            $bar .= $arrow_r;
        }
        $bar .= "</ul></div>";

        return $bar;
    }

    /**
     * Public access for the HTML pagination bar.
     *
     * @return string HTML for the pagination bar.
     */
    public function return_pagination() {
        return $this->pagination_bar;
    }
}
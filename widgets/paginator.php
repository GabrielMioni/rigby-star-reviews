<?php

require_once('abstract_widget.php');

class paginator extends abstract_widget
{
    protected $buttons_per_bar;
    protected $pagination_chunks;
    protected $chunk_pointer;
    protected $query_string;
    protected $ajax_url;

    protected $pagination_bar;

    public function __construct(array $setting_array = array())
    {
        $page             = $this->check_setting_element($setting_array, 'page');
        $reviews_per_page = $this->check_setting_element($setting_array, 'reviews_per_page');
        $rating           = $this->check_setting_element($setting_array, 'rating');
        $product_id       = $this->check_setting_element($setting_array, 'product_id');

        parent::__construct($page, $reviews_per_page, $rating, $product_id);

        $this->buttons_per_bar   = $this->set_buttons_per_bar($setting_array);
        $this->ajax_url          = $this->check_setting_element($setting_array, 'ajax_url');

        $this->pagination_chunks = $this->set_pagination_chunks($this->last_page, $this->buttons_per_bar);
        $this->chunk_pointer     = $this->set_chunk_pointer($this->page, $this->pagination_chunks);
//        $this->query_string      = $this->set_url_query_string($this->rating, $this->product_id);
        $this->query_string      = $this->set_url_query_string();

        $this->pagination_bar = $this->build_pagination_bar($this->page, $this->last_page, $this->pagination_chunks, $this->ajax_url, $this->query_string);
    }

    protected function set_buttons_per_bar(array $setting_array)
    {
        $bpb = $this->check_setting_element($setting_array, 'buttons_per_bar');

        return $this->set_int_value($bpb, 10);
    }

    protected function set_int_value($int_val, $if_empty_val)
    {
        $out = $if_empty_val;
        if (trim($int_val !== ''))
        {
            $out = (int)$int_val;
        }
        if ($out < 1)
        {
            $out = 1;
        }
        return $out;
    }

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

    protected function set_url_query_string()
    {
        $rating = $this->rating;
        $product_id = $this->product_id;

        $query_string = '';

        switch ($rating)
        {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
                $query_string .= '&rating=' . $rating;
                break;
            default:
                break;
        }
        switch ($product_id)
        {
            case '':
                break;
            default:
                $query_string .= '&product=' . $product_id;
                break;
        }
        return $query_string;
    }

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

    public function return_pagination()
    {
        return $this->pagination_bar;
    }
}
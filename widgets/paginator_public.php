<?php
if (!defined('RIGBY_ROOT')) {
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/abstract/abstract_paginator.php');
require_once(RIGBY_ROOT . '/widgets/abstract/trait_visitor_navigate.php');
require_once(RIGBY_ROOT . '/widgets/abstract/trait_data_collect.php');

//require_once('abstract/abstract_paginator.php');
//require_once('abstract/trait_data_collect.php');

/**
 * Builds HTML for the paginator used to browse public review data.
 *
 * All
 */
class paginator_public extends abstract_paginator {

    use trait_visitor_navigate;

    protected $rating;
    protected $product_id;

    public function __construct(array $settings_array = array())
    {
        $this->rating       = $this->check_settings_and_get($settings_array, 'rating');
        $this->product_id   = $this->check_settings_and_get($settings_array, 'product_id');
        parent::__construct($settings_array);
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
}
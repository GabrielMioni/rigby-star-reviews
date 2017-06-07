<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}

require_once(RIGBY_ROOT . '/widgets/abstract/abstract_paginator.php');
require_once('trait_product_select.php');

class products_admin_paginator extends abstract_paginator
{
    use trait_product_select;

    public function __construct(array $setting_array = array())
    {
        $setting_array['results_per_page'] = 10;
        parent::__construct($setting_array);
    }

    protected function set_result_count()
    {
        $count = $this->product_select(0);
        return $count;
    }

    protected function set_url_query_string()
    {
        $query_string = '';

        if (isset($_GET['product_name']))
        {
            $query_string .= 'product_name=' . htmlspecialchars($_GET['product_name']);
        }
        if (isset($_GET['product_id']))
        {
            $query_string .= 'product_id=' . htmlspecialchars($_GET['product_id']);
        }

        return $query_string;
    }
}

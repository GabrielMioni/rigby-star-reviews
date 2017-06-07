<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once('trait_review_select.php');
require_once(RIGBY_ROOT . '/widgets/abstract/abstract_paginator.php');

class paginator_review_admin extends abstract_paginator
{
    use trait_review_select;

    public function __construct(array $setting_array = array())
    {
        $this->construct_input_arrays();

        $results_per_page = $this->set_results_per_page();

        $setting_array['results_per_page'] = $results_per_page;
        parent::__construct($setting_array);
    }

    protected function set_result_count()
    {
        $input_array = $this->input_array;
        $date_inputs = $this->date_inputs;
        $star_inputs = $this->star_inputs;

        $count = $this->review_select('COUNT(*)', $input_array, $date_inputs, $star_inputs);

        return $count;
    }

}

// $test = new review_admin_table();
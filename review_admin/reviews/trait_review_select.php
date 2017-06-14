<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

require_once(RIGBY_ROOT . '/widgets/abstract/trait_data_collect.php');
require_once(RIGBY_ROOT . '/widgets/abstract/trait_append_query_data.php');


trait trait_review_select
{
    use trait_data_collect;
    use trait_append_query_data;

    protected $input_array = array();
    protected $date_inputs = array();
    protected $star_inputs = array();

    protected function construct_input_arrays()
    {
        $this->push_input_array($this->input_array, 'title', 'title_search');
        $this->push_input_array($this->input_array, 'name',  'name_search');
//        $this->push_input_array($this->input_array, 'product', 'product_search');
        $this->push_input_array($this->input_array, 'email', 'email_search');
        $this->push_input_array($this->input_array, 'ip',    'ip_search');

        $this->push_input_array($this->date_inputs, 'date_range',  'date_range');
        $this->push_input_array($this->date_inputs, 'date_single', 'date_single');
        $this->push_input_array($this->date_inputs, 'date_start',  'date_start');
        $this->push_input_array($this->date_inputs, 'date_end',    'date_end');

        $this->push_input_array($this->star_inputs, 1, 'star-1');
        $this->push_input_array($this->star_inputs, 2, 'star-2');
        $this->push_input_array($this->star_inputs, 3, 'star-3');
        $this->push_input_array($this->star_inputs, 4, 'star-4');
        $this->push_input_array($this->star_inputs, 5, 'star-5');
    }

    protected function push_input_array(&$input_array, $input_index, $get_index)
    {
        $push_element = $this->check_get('', $get_index);
        if ($push_element !== false || trim($push_element) !== '')
        {
            $input_array[$input_index] = $push_element;
        }
    }

    protected function review_select($select_type, array $input_array, array $date_inputs, array $star_inputs, $sql_start = null, $results_per_page = null) {

        $pdo = array();

        $query  = "SELECT $select_type FROM star_reviews";
        $query .= (!empty($input_array) || !empty($date_inputs) || !empty($star_inputs)) ? ' WHERE' : '';

        $this->set_query_from_inputs($input_array, $query, $pdo);
        $this->set_query_from_stars($star_inputs, $query, $pdo);
        $this->set_query_from_dates($date_inputs, $query, $pdo);

        $query = rtrim($query, ' AND');
        $query = rtrim($query, ' WHERE');

        if ($select_type !== 'COUNT(*)')
        {
            $query .= '  ORDER BY date desc';
        }

        if ($sql_start !== null && $results_per_page !== null)
        {
            $query .= " LIMIT ?, ?";
            $pdo[] = $sql_start;
            $pdo[] = $results_per_page;
        }

        try {
            if ($select_type == 'COUNT(*)')
            {
                $reviews = sql_pdo::run($query, $pdo)->fetchColumn();
            } else {
                $reviews = sql_pdo::run($query, $pdo)->fetchAll();
            }
            return $reviews;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    protected function set_query_from_inputs(array $input_array, &$query, &$pdo)
    {
        if (empty($input_array))
        {
            return false;
        }

        foreach ($input_array as $key=>$value)
        {
            if (trim($value) !== '')
            {
                $query_append = " $key = ? AND";
                $query .= $this->append_query_data($value, $query_append, $pdo);
            }
        }
    }

    protected function set_query_from_stars(array $star_array, &$query, &$pdo)
    {

        if (empty($star_array))
        {
            return false;
        }

        $stars = array_keys($star_array);

        $star_query = '';

        foreach ($stars as $value)
        {
            $star_query .= " stars = ? OR";
            $pdo[] = $value;
        }

        $star_query = rtrim($star_query, ' OR');
        $star_query .= ' AND';
        $query .= $star_query;
    }

    protected function set_query_from_dates(array $date_inputs, &$query, &$pdo)
    {
        if (empty($date_inputs))
        {
            return false;
        }

        $range_set   = $this->evaluate_date_inputs($date_inputs, 'date_range');
        $date_single = $this->evaluate_date_inputs($date_inputs, 'date_single');
        $start_set   = $this->evaluate_date_inputs($date_inputs, 'date_start');
        $end_set     = $this->evaluate_date_inputs($date_inputs, 'date_end');

        if ($range_set == false)
        {
            return false;
        }

        $process_type = null;
        $unix_start = null;
        $unix_end   = null;

        $process_set = null;
        if (isset($_GET['date_range'])) {
            $process_set = htmlspecialchars($_GET['date_range']);
        }

        switch ($process_set)
        {
            case 'date_single':
                $process_type = 1;
                break;
            case 'date_range':
                $process_type = 2;
                break;
            default:
                break;
        }

        if ($process_type == 1 && $date_single == true)
        {
            // date single process
            $date_start = date('m/d/y', strtotime($date_inputs['date_single']));

            $unix_start = strtotime($date_start);
            $unix_end   = $unix_start + 86400; // 24 hours.
        }
        if ($process_type == 2 && $start_set == true && $end_set == true)
        {
            $date_start = date('m/d/y', strtotime($date_inputs['date_start']));
            $date_end   = date('m/d/y', strtotime($date_inputs['date_end']));

            $unix_start = strtotime($date_start);
            $unix_end   = strtotime($date_end);
        }

        if ($unix_start !== null && $unix_end !== null)
        {
            $sql_format = 'Y-m-d H:i:s';
            $sql_date_start = date($sql_format, $unix_start);
            $sql_date_end   = date($sql_format, $unix_end);

            $query .= " date > ? and date < ?";

            $pdo[] = $sql_date_start;
            $pdo[] = $sql_date_end;

        }
    }

    protected function evaluate_date_inputs($date_input_array, $date_input_index)
    {
        if (isset($date_input_array[$date_input_index]))
        {
            if (trim($date_input_array[$date_input_index]) == '')
            {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    protected function set_results_per_page()
    {
        $results_per_page = $this->check_get('', 'page_p');

        switch ($results_per_page)
        {
            case 10:
            case 20:
            case 50:
            case 100:
            case 1000:
                return $results_per_page;
                break;
            default:
                return 10;
        }
    }

    protected function set_url_query_string()
    {
        $input_array = $this->input_array;
        $date_inputs = $this->date_inputs;
        $star_inputs = $this->star_inputs;

        $page_p = '';

        if (isset($_GET['page_p']))
        {
            $page_p .= 'page_p=' . htmlspecialchars($_GET['page_p']);
        }

        $query_string = '';

        foreach ($input_array as $key=>$value)
        {
            $query_string .= $key . '_search=' . $value . '&';
        }

        foreach ($date_inputs as $key=>$value)
        {
            $query_string .= "$key=$value" . '&';
        }

        foreach ($star_inputs as $key=>$value)
        {
            $query_string .= "star-$key=on&";
        }
        $query_string .= $page_p;
        $query_string = rtrim($query_string, '&');

        return $query_string;
    }
}
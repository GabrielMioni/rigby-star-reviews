<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

class products_update
{
    protected $is_ajax;
    protected $column;
    protected $sql_id;
    protected $pdo_array = array();

    protected $affected_reviews = 0;
    protected $problems = array();
    protected $display_errors = array();

    protected $response;

    public function __construct()
    {
        $this->is_ajax = isset($_POST['ajax_submit']) ? true : false;
        $this->sql_id  = isset($_POST['id']) ? htmlspecialchars($_POST['id']) : false;

        $this->set_pdo_elm($this->pdo_array, 'product_id');
        $this->set_pdo_elm($this->pdo_array, 'product_name');

        $this->update_products_table($this->is_ajax, $this->sql_id, $this->pdo_array);

        $this->write_errors($this->problems);
        $this->set_response($this->display_errors, $this->affected_reviews, $this->pdo_array);
    }

    protected function set_pdo_elm(&$pdo_array, $post_key)
    {
        if (isset($_POST[$post_key])) {
            $post_data = $_POST[$post_key];

            switch (trim($post_data))
            {
                case '':
                    $column_name = $this->format_column_name($post_key);
                    $this->display_errors[] = "$column_name cannot be blank.";
                    break;
                default:
                    $pdo_array[$post_key] = htmlspecialchars($_POST[$post_key]);
                    break;
            }
        }
    }

    protected function update_products_table($is_ajax, $sql_id, array $pdo_array)
    {
        $pdo_not_empty = !empty($pdo_array) == true;

        $query = "UPDATE products SET ";
        $clean_pdo  = array();
        $query_data = array();

        $updated_products_flag = false;

        // Check $pdo_array's keys to make sure they're valid and JavaScript isn't sending columns we would never
        // want to update. We're building a new array here with keys that have been validated.
        foreach ($pdo_array as $key=>$value)
        {
            $set_key = $this->set_column($key);

            if ($set_key !== false)
            {
                $clean_pdo[$set_key] = $value;
            } else {
                $clean_pdo['bad'] = false;
            }
        }

        /* Check to make sure no invalid indexes were passed into $clean_pdo. */
        $pdo_is_clean = !in_array(false, $clean_pdo);

        $problems_flag = false;

        if ($is_ajax == true && $pdo_not_empty == true && $pdo_is_clean == true && $sql_id !== false)
        {
            foreach ($clean_pdo as $key=>$value)
            {
                $query .= " $key = ?,";
                $query_data[] = $value;
            }
            $query_data[] = $sql_id;
            $query = rtrim($query, ',');
            $query .= " WHERE id=?";

            try
            {
                sql_pdo::run($query, $query_data);
                $updated_products_flag = true;
            } catch (PDOException $e) {
                $this->problems[] = $e->getMessage();
            }
        } else {
            /* If there were any problems, set a display error message */
            $problems_flag = true;
        }

        /* If the products.sql table was successfully updated go ahead and update the reviews table too */
        if ($updated_products_flag == true)
        {
            $update_reviews = $this->update_reviews_table($sql_id, $clean_pdo);

            if ($update_reviews == false)
            {
                $this->problems[] = 'Unable to update review table';
                $problems_flag = true;
            }
        }
/*
        if ($problems_flag == true)
        {
            $this->display_errors[] = 'Your request coudn\'t be processed';
        }
*/
        /* Evaluate whether problems were found and set errors */
        if ($is_ajax == false) { $this->problems[] = 'Request is not ajax';
        }
        if ($pdo_not_empty == false) { $this->problems[] = 'No $_POST data present';
        }
        if ($pdo_is_clean == false) { $this->problems[] = 'Invalid fields were sent';
        }
        if ($sql_id == false) { $this->problems[] = 'Invalid ID sent';
        }
    }

    protected function update_reviews_table($sql_id, array $pdo_array)
    {
        $check_prod_id = array_key_exists('product_id', $pdo_array);

        $original_prod_id = $this->get_original_product_id($sql_id);

        if ($check_prod_id == true && $original_prod_id !== false)
        {
            $product_id = $pdo_array['product_id'];

            $query = "UPDATE star_reviews SET product = ? WHERE product = ?";

            try {
                $update = sql_pdo::run($query, [$product_id, $original_prod_id]);
                $this->affected_reviews = $update->rowCount();
                return true;
            } catch (PDOException $e) {
                $this->problems[] = $e->getMessage();
                return false;
            }
        }
        return true;
    }

    protected function set_column($column_key)
    {
        switch ($column_key)
        {
            case 'product_name':
                return 'product_name';
            case 'product_id':
                return 'product_id';
            default :
                $this->display_errors = htmlspecialchars($column_key) . ' is an invalid column type.';
                return false;
        }
    }

    protected function get_original_product_id($sql_id)
    {
        $query = "SELECT product_id WHERE id=?";
        try
        {
            $result = sql_pdo::run($query, [$sql_id])->fetchColumn();
            if ($result !== '')
            {
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function set_response(array $display_errors, $affected_reviews, array $pdo_array)
    {
        $tmp = array();

        if (!empty($display_errors))
        {
            $tmp[] = 0;
            foreach ($display_errors as $errors)
            {
                $tmp[] = $errors;
            }
        } else {
            $tmp[] = 1;
            $message = '';
            $keys = array_keys($pdo_array);

            $plural = 0;

            foreach ($keys as $column_key)
            {
                $column_name = $this->format_column_name($column_key);
//                $message .= $column_name . ' and ';
                $message .= $column_name;

                if (!strpos($message, 'and')) {
                    $message .= ' and ';
                }

                ++$plural;
            }
            if ($plural > 1)
            {
                $plural_msg = 'were';
            } else {
                $plural_msg = 'has been';
            }
            $message .= " $plural_msg updated!";

            $keys = array_keys($pdo_array);
            if (in_array('product_id', $keys))
            {
                $message .= ' Reviews affected: ' . $affected_reviews . '.';
            }

            $tmp[] = $message;
        }
        echo json_encode($tmp);
    }

    protected function write_errors(array $problems)
    {
        foreach ($problems as $prob)
        {
            $message = date('Y-m-d H:i:s') . $prob;
            error_log($message);
        }

    }

    protected function format_column_name($column_name)
    {
        return ucwords(str_replace('_', ' ', $column_name));
    }
}

new products_update();
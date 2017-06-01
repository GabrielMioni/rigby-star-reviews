<?php
session_start();
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
require_once(RIGBY_ROOT . '/review_admin/php/edit_abstract.php');

class add_products extends edit_abstract
{
    protected $is_ajax;

    protected $numeric_keys = array();
    protected $id_name_pair = array();

    protected $errors = array();
    protected $problem = null;
    
    public function __construct($is_ajax = false)
    {
        parent::__construct();

        $this->is_ajax = $is_ajax;

        $this->numeric_keys = $this->set_numeric_keys();
        $this->id_name_pair = $this->set_id_name_pairs($this->numeric_keys);

        $this->insert_products($this->id_name_pair, $this->admin_valid);
        $this->set_message($this->is_ajax, $this->problem);
        $this->go_to_products_table($this->is_ajax);
    }

    /**
     * Searches $_POST data for keys that start 'product_id_add' and 'product_name_add'. If found, collects
     * the prepended numeric value. Example: 'product_name_add_3' would be collected as '3'.
     *
     * The numeric values that are found are set in an array. Only unique values are returned. These numeric elements
     * are used to check both product_id and product_name type elements later.
     *
     * @return array Array of numeric values.
     */
    protected function set_numeric_keys()
    {
        $post_keys = array_keys($_POST);
        $numeric_keys = array();

        foreach ($post_keys as $key)
        {
            $check_id_key   = strpos($key, 'product_id_add');
            $check_name_key = strpos($key, 'product_name_add');

            if ($check_id_key !== false)
            {
                $key_number = $this->get_numerics($key);
            }
            if ($check_name_key !== false)
            {
                $key_number = $this->get_numerics($key);
            }
            $numeric_keys[] = $key_number[0];
        }
        $numeric_keys = array_unique($numeric_keys);
        return $numeric_keys;
    }

    /**
     * Searches a string for a numeric value and returns that numeric value.
     *
     * @param $str string The string the method is searching for a numeric value.
     * @return int Returns a numeric value if found.
     */
    function get_numerics ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return $matches[0];
    }

    /**
     * Checks $_POST data for 'product_id_add_' and product_name_add_' indexes. Uses the $numeric_keys to find
     * specific indexes. The goal is to match id / name pairs to be inserted into the products.sql table.
     *
     * If either id or name values for a given pair are empty, neither will be inserted.
     *
     * @param array $numeric_keys The numeric elements found by add_products::set_numeric_keys().
     * @return array
     */
    protected function set_id_name_pairs(array $numeric_keys)
    {
        // Holds array elements with id / name paris.
        $id_name_pairs = array();

        foreach ($numeric_keys as $number_id)
        {
            // Temp array to hold id / name values. These are added to $id_name_pairs if both values are good.
            $tmp = array();

            // Set the index names for the $_POST elements that need to be checked.
            $product_id_key   = 'product_id_add_' . $number_id;
            $product_name_key = 'product_name_add_' . $number_id;

            // Initialize flag. If true, id / name values will be set. If either id or name values are empty,
            // the values will not be set.
            $post_values_good = true;
            $product_id_value = false;
            $product_name_value = false;

            if (isset($_POST[$product_id_key]) && isset($_POST[$product_name_key]))
            {
                $product_id_value   = $this->set_post_value($_POST[$product_id_key]);
                $product_name_value = $this->set_post_value($_POST[$product_name_key]);

                // If either $_POST inputs were empty, do not add the id/name pair.
                if ($product_id_value == false || $product_name_value == false)
                {
                    $post_values_good = false;
                }
            }

            if ($post_values_good == true)
            {
                $tmp[] = $product_id_value;
                $tmp[] = $product_name_value;
                $id_name_pairs[] = $tmp;
            }
        }
        return $id_name_pairs;
    }

    /**
     * Checks the argument $post to see if it's empty. If so, return false. Else, return cleaned $post value.
     *
     * @param $post string
     * @return bool|string False if $post is empty. Else, return cleaned $post.
     */
    protected function set_post_value($post)
    {
        $post = htmlspecialchars($post);
        if (trim($post) == '')
        {
            return false;
        } else {
            return $post;
        }
    }

    protected function insert_products(array $id_name_pair, $admin_valid)
    {
        // Prepared statement multiple execution
        $query = "INSERT INTO products (product_id, product_name) VALUES (?, ?)";

        if ($admin_valid == true)
        {
            try {
                $stmt = sql_pdo::prepare($query);
                foreach ($id_name_pair as $id_and_name)
                {
                    $prod_id = $id_and_name[0];
                    $prod_name = $id_and_name[1];
                    $stmt->execute([$prod_id, $prod_name]);
                }
            } catch (PDOException $e) {
                $this->problem = "Your request could not be processed";
                error_log($e->getMessage());
            }
        } else {
            $this->problems = "You do not have the privileges to change products. See your administrator";
        }
    }

    protected function set_message($is_ajax, $problems)
    {
        if ($is_ajax == false)
        {
            session_start();
            switch ($problems)
            {
                case NULL:
                    $_SESSION['product_add'] = '<div class="good">Products added!</div>';
                    break;
                default:
                    $_SESSION['product_add'] = '<div class="error">There was a problem processing your request.</div>';
                    break;
            }
        } elseif ($is_ajax == true) {
            $msg_array = array();
            switch ($problems)
            {
                case NULL:
                    $msg_array[0] = 1;
                    $msg_array[1] = 'Products added!';
                    break;
                default:
                    $msg_array[0] = 0;
                    $msg_array[1] = 'There was a problem processing your request.';
                    break;
            }
            $json_encoded_msg = json_encode($msg_array);
            echo $json_encoded_msg;
        }
    }

    protected function go_to_products_table($is_ajax)
    {
        if (!$is_ajax)
        {
            $referer = htmlspecialchars($_SERVER['HTTP_REFERER']);
            header("Location: $referer");
        }
    }
}

new add_products();
<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT .'/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT .'/php/sql_pdo/sql_pdo.php');

class products_delete
{
    protected $is_ajax;
    protected $pdo_array = array();

    protected $affected_reviews = 0;
    protected $deleted_count    = 0;
    protected $deleted_products = array();
    protected $problems         = array();

    public function __construct($is_ajax = false)
    {
        $this->is_ajax = $is_ajax;
        $this->pdo_array = $this->set_pdo_array();

        $this->deleted_products = $this->get_names_and_prod_ids($this->pdo_array);

        $this->run_products_delete($this->pdo_array);
        $this->set_message($this->deleted_count, $this->problems, $this->deleted_products);
        $this->go_to_products_table($this->is_ajax);
    }
    protected function set_pdo_array()
    {
        $tmp = array();
        if (isset($_POST['prod_id']))
        {
            foreach ($_POST['prod_id'] as $prod_id)
            {
                $tmp[] = (int)htmlspecialchars($prod_id);
            }
        }
        return $tmp;
    }
    protected function get_names_and_prod_ids(array $pdo_array)
    {
        $tmp = array();

        if (empty($pdo_array))
        {
            return $tmp;
        }
        
        $query = "SELECT product_id, product_name FROM products WHERE ";

        foreach ($pdo_array as $pdo_elm)
        {
            $query .= "id = ? OR ";
        }
        $query = rtrim($query, ' OR ');

        try {
            $results = sql_pdo::run($query, $pdo_array)->fetchAll();
            $tmp = $results;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->problems[] = 'Your request could not be processed';
        }
        return $tmp;
    }

    protected function run_products_delete(array $pdo_array)
    {
        $query = "DELETE FROM products WHERE id = ?";
        $pdo_count = count($pdo_array);

        $successful_delete = false;

        for ($p = 0 ; $p < $pdo_count-1 ; ++$p)
        {
            if ($p !== $pdo_count)
            {
                $query .= ' OR id = ?';
            }
        }

        try {
            sql_pdo::run($query, $pdo_array);
            $this->deleted_count = $pdo_count;
            $successful_delete = true;

        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        if ($successful_delete == true)
        {
            try {
                $query = "UPDATE star_reviews SET product = '' WHERE product = ?";

                $stmt = sql_pdo::prepare($query);
                foreach ($pdo_array as $id)
                {
                    $stmt->execute([$id]);
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $this->problems[] = "There was a problem processing your request";
            }
        }
    }

    protected function set_message($deleted_count, array $problems, array $deleted_products)
    {
        /*
        if (empty($problems))
        {
            $message  = $deleted_count . ' deleted';
        } else {
            $message = 'There was a problem processing your request.';
        }
        */
        $message = '';

        if (empty($problems) && !empty($deleted_products)) {

            $message .= "<div class='deleted_count'>Deleted $deleted_count</div>";
            $message .= '<table>';
            $message .= '<thead>';
            $message .= '<tr><td>Product Id</td><td>Product_Name</td></tr>';
            $message .= '</thead>';
            $message .= '<tbody>';

            foreach ($deleted_products as $product_array)
            {
                $product_id = $product_array['product_id'];
                $product_name = $product_array['product_name'];

                $message .= "<tr><td>$product_id</td><td>$product_name</td></tr>";
            }

            $message .= '</tbody>';
            $message .= "</table>";
        }

        session_start();
        if (empty($problems)) {
            $_SESSION['success_message'] = $message;
        } else {
            $_SESSION['failure_message'] = $message;
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

new products_delete();
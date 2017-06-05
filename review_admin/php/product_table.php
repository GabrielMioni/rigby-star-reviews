<?php
require_once('products_search.php');

class product_table {

    protected $product_name;
    protected $product_id;
    protected $date_set;
    protected $date_start;
    protected $date_end;
    protected $page;
    protected $results_per_page;
    protected $query_is_count = false;

    protected $results;

    protected $table;

    public function __construct($product_name, $product_id, $date_set, $date_start, $date_end, $page, $results_per_page)
    {
        $this->product_name = $this->check_input($product_name);
        $this->product_id   = $this->check_input($product_id);
        $this->date_set     = $this->check_input($date_set);
        $this->date_start   = $this->check_input($date_start);
        $this->date_end     = $this->check_input($date_end);
        $this->page         = $this->check_input($page);
        $this->results_per_page = $this->check_input($results_per_page);

//        $this->results = $this->set_results($this->product_name, $this->product_id, $this->date_set, $this->date_start, $this->date_end, $this->page, $this->results_per_page);
        $this->results = $this->set_results($this->product_name, $this->product_id, $this->page, $this->results_per_page);

        $this->table = $this->build_table($this->results);

    }

    protected function check_input($input)
    {
        if (trim($input) == '')
        {
            return '';
        } else {
            return htmlspecialchars($input);
        }
    }

//    protected function set_results($product_name, $product_id, $date_set, $date_start, $date_end, $page, $results_per_page)
    protected function set_results($product_name, $product_id, $page, $results_per_page)
    {
        $pdo = array();
        
        $row_start = $this->set_start($page, $results_per_page);
        $row_end   = $row_start + $results_per_page;
        
        $query = '  SELECT  p.id, p.product_name, p.product_id, p.create_date, p.last_review, COUNT(r.product) as review_count
                    FROM    products p
                    LEFT JOIN
                            star_reviews r
                    ON      r.product = p.product_id';
        if ($product_name !== '')
        {
            $query .= ' WHERE p.product_name = ? AND';
            $pdo[] = $product_name;
        }
        if ($product_id !== '')
        {
            $query .= ' WHERE p.product_id = ?';
            $pdo[] = $product_id;
        }
        $query = rtrim($query, ' AND');
        $query .= " GROUP BY p.product_id ORDER BY p.id LIMIT ?, ?";
        $pdo[] = $row_start;
        $pdo[] = $results_per_page;

        try {
            $results = sql_pdo::run($query, $pdo)->fetchAll();
            return $results;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return array();
        }
    }
    
    protected function push_pdo($value)
    {
        
    }
        

    protected function set_start($page, $results_per_page)
    {
        if ($page <= 0) {
            return 0;
        }
        return ($page - 1) * $results_per_page;
    }

    protected function build_table(array $results)
    {
        $table = '<table id="product_table">';

        $thead  = '<thead><tr class="head"><th><input class=\'checkbox_toggle\' type=\'checkbox\'></th><th id="th_prod_id">Product ID</th><th id="th_prod_name">Product Name</th><th id="update_header"></th></th><th>Review Count</th><th>Create Date</th><th>Last Review</th><th></th></tr></thead>';
        $thead .= '  <script type="text/javascript">
                        $(".checkbox_toggle").show();
                    </script>';

        $tbody = '<tbody>';

        $colspan_count = substr_count($thead, '<th');

        foreach ($results as $row)
        {

            $id           = $row['id'];
            $prod_id      = $row['product_id'];
            $prod_name    = $row['product_name'];
            $review_count = $row['review_count'];
            $created      = $row['create_date'];
            $last_review  = $row['last_review'];

            $edit_button = "<td class=\"edit_button\"><a href=\"detail_edit.php?id=$id\"><div class=\"tog pencil\"></div></a></td>";

            $submit_button = "<td class='update_submit'><button name='update' class='edit_submit'>Update</button></td>";

            $tr  = "<tr class='row_$id'><td><input type='checkbox' name='prod_id[]' value='$id'> </td></td><td class='product_id'>$prod_id</td><td class='product_name'>$prod_name</td>$submit_button<td>$review_count</td><td>$created</td><td>$last_review</td>$edit_button</tr>";
            $tr .= "<tr class='message'><td colspan=\"$colspan_count\"></td></tr>";

            $tbody .= $tr;
        }
        $tbody .= '</tbody>';

        $table .= $thead;
        $table .= $tbody;
        $table .= '</table>';

        $form_table = "    <form id='product_form' action='php/products_delete.php' method='post'>
                                $table
                                <div class='form_row'>
                                    <input type='submit' value='Delete Selected Products'>
                                </div>                                
                           </form>";

//        return $table;
        return $form_table;
    }

    public function return_table()
    {
        return $this->table;
    }
}
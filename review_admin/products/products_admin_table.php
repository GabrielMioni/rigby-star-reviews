<?php

if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/abstract/abstract_navigate.php');
require_once('trait_product_select.php');

class products_admin_table extends abstract_navigate
{
    use trait_product_select;

    protected $product_array = array();
    protected $form_table;

    public function __construct()
    {
        $page = $this->construct_get_page();
        $results_per_page = 10;

        parent::__construct($page, $results_per_page);

        $this->product_array = $this->product_select(1, $this->sql_start, $results_per_page);

        $this->form_table = $this->build_table($this->product_array);
    }

    protected function set_result_count()
    {
        $count = $this->product_select(0);
        return $count;
    }

    protected function construct_get_page()
    {
        $page = $this->check_get('', 'page');

        return $this->set_int_value($page, 1);
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
        return $form_table;
    }

    public function return_table()
    {
        return $this->form_table;
    }
}

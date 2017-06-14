<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/abstract/abstract_navigate.php');
require_once(RIGBY_ROOT . '/review_admin/reviews/trait_review_select.php');
require_once(RIGBY_ROOT . '/review_admin/php/check_admin.php');

class review_admin_table extends abstract_navigate
{
    use trait_review_select;
    use check_admin;

    protected $sql_date_format;

    protected $username;
    protected $password;
    protected $view_privilege = null;

    protected $review_array = array();

    protected $query_string;
    protected $table;

    public function __construct(array $setting_array = array())
    {
        $this->username = isset($_SESSION['username']) ? $_SESSION['username'] : false;
        $this->password = isset($_SESSION['password']) ? $_SESSION['password'] : false;

        $this->view_privilege = $this->privilege_check($this->username, $this->password);
        $this->view_privilege = true;
        $this->reject_bad_users($this->view_privilege);

        $page = $this->construct_get_page();
        $results_per_page = $this->set_results_per_page();
        parent::__construct($page, $results_per_page);

        $this->construct_input_arrays();
        $this->query_string = $this->set_url_query_string();

        $this->review_array = $this->review_select('*', $this->input_array, $this->date_inputs, $this->star_inputs, $this->sql_start, $this->results_per_page);

        $this->table = $this->build_table($this->review_array, $this->query_string);
    }

    protected function privilege_check($username, $password) {
        $chk_user = $this->chk_admin_creds($username, $password);
        switch ($chk_user) {
            case TRUE:
                $chk_priv = $this->chck_admin_priv($username);
                return $chk_priv;
            default:
                return FALSE;
        }
    }

    protected function reject_bad_users($view_privilege)
    {
        switch ($view_privilege)
        {
            case false:
                header("Location: HTTP/1.0 404 Not Found");
                exit;
                break;
            default:
                break;
        }
    }

    protected function construct_get_page()
    {
        $page = $this->check_get('', 'page');

        return $this->set_int_value($page, 1);
    }

    protected function set_result_count()
    {
        $input_array = $this->input_array;
        $date_inputs = $this->date_inputs;
        $star_inputs = $this->star_inputs;

        $count = $this->review_select('COUNT(*)', $input_array, $date_inputs, $star_inputs);

        if (empty($count) || $count === 0)
        {
            return 0;
        }

        if ($count === false)
        {
            $error_msg = "review_admin_table couldn't get review data: ";
            trigger_error($error_msg);
        } else {
            return $count;
        }
    }

    protected function build_table($review_array, $query_string)
    {

        $thead = "  <thead>
                        <tr class='head'>
                            <th id='col_id'>Id</th>
                            <th id='product'>Product</th>
                            <th id='col_stars'>Stars</th>
                            <th id='col_title'>Title</th>
                            <th id='col_name'>Name</th>
                            <th id='col_email'>Email</th>
                            <th id='col_date'>Date</th>
                            <th>IP</th>
                            <th id='col_hidden'>Hidden</th>
                            <th></th>
                        </tr>
                    </thead>";

        $tbody = "<tbody>";

        foreach ($review_array as $key => $row) {
            $id     = $row['id'];
            $prod   = $row['product'];
            $title  = $row['title'];
            $name   = $row['name'];
            $email  = $row['email'];
            $cont   = $row['cont'];
            $ip     = $row['ip'];
            $hidden = $row['hidden'];
            $date   = $row['date'];
            $stars  = $row['stars'];

            $odd_check  = $this->check_odd($key);

            $star_divs  = $this->set_stars($stars);
            $sel_star   = $this->set_star_sel($stars);
            $hidden_set = $this->set_hidden($hidden, 'Yes', 'No');
            $hidden_chk = $this->set_hidden($hidden, ' checked', '');
            $display_cont = $this->set_content($cont);
            $display_date = $this->set_date($date);


            $detail_edit_url = "detail_edit.php?id=$id&$query_string";
            if ($this->view_privilege == true)
            {
                $edit_button = "<input name='edit_submit' class='edit_submit' type='submit' value='Update'>";
                $detail_link = "<div class='form_row'>
                                    <a class='detail_link' href='$detail_edit_url'>Go to detail edit</a>
                                </div>";
            } else {
                $edit_button = '';
                $detail_link = '';
            }

            $row_disp = "<tr class='row_display$odd_check'>
                            <td class='disp_id'><a name='row_$id'>$id</a></td>
                            <td class='disp_prod'>$prod</td>
                            <td class='disp_stars'>$star_divs</td>
                            <td class='disp_title'>$title</td>
                            <td class='disp_name'>$name</td>
                            <td class='disp_email'>$email</td>
                            <td class='disp_date'>$display_date</td>
                            <td class='disp_ip'>$ip</td>
                            <td class='disp_hidden' >$hidden_set</td>
                            <td class='edit_button'>
                              <a href='$detail_edit_url'><div class='tog pencil'></div></a>
                            </td>
                        </tr>";

            // Used to set <td> colspan in the row_edit row.
            $td_count = substr_count($row_disp, '</td>');

            $row_edit = "<tr class='row_edit$odd_check'>
                            <td colspan='$td_count'>
                                <form action='php/edit_quick_act.php' method='post'>
                                    <input name = 'id' id='id' type='hidden' value='$id'>
                                    <div class='form_left'>
                                        <div class='form_row'>
                                            <label for='title'>Title:</label>
                                            <input name='title' class='title_edit' type='text' value='$title'>
                                        </div>
                                        <div class='form_row'>
                                            <label for='name'>Name:</label>
                                            <input name='name' class='name_edit' type='text' value='$name'> 
                                        </div>
                                        <div class='form_row'>
                                            <label for='email'>Email:</label>
                                            <input name='email' class='email_edit' type='text' value='$email'>
                                        </div>
                                        <div class='form_row'>
                                            <label for='stars'>Stars:</label>
                                            <select name='stars' class='stars_edit'>
                                                <option value='5'$sel_star[5]>★★★★★</option>
                                                <option value='4'$sel_star[4]>★★★★ </option>
                                                <option value='3'$sel_star[3]>★★★  </option>
                                                <option value='2'$sel_star[2]>★★   </option>
                                                <option value='1'$sel_star[1]>★    </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class='form_right'>
                                        <label for='cont'>Content:</label>
                                        <textarea name='cont' class='content_edit'>$display_cont</textarea>
                                        <div class='form_row'>
                                            <label for='hidden'>Hidden:</label>
                                            <input name='hidden' class='hidden_edit' type='checkbox'$hidden_chk>
                                        </div>
                                        $detail_link
                                    </div>
                                    $edit_button
                                    <input name='change' type='hidden' value='0'>
                                    <div class='update_result'>
                                        <div class='spinner'>
                                            <img src='../imgs/circle-ball.gif'>    
                                        </div>
                                        <div class='check_div'>
                                            <i class='fa fa-check' aria-hidden='true'></i>
                                        </div>
                                        <div class='no_change'>
                                            Make a change before updating.
                                        </div>
                                        <div class='ajax_fail'>
                                            Fields cannot be blank and must be in valid format.
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>";

            $tbody .= $row_disp;
            $tbody .= $row_edit;
        }
        $tbody .= '</tbody>';

        $table  = "<table id='review_admin'>";
        $table .= $thead;
        $table .= $tbody;
        $table .= "</table>";

        return $table;
    }

    /**
     * Returns star_full divs equal to the number value of the method argument.
     *
     * star_full divs are used to display .png stars to represent the review rating.
     *
     * @param $stars integer
     * @return string
     */
    protected function set_stars($stars) {
        $star_return = '';
        for ($s = 0 ; $s < $stars ; ++$s) {
            $star_return .= "<div class='star_full'></div>";
        }
        return $star_return;
    }

    /**
     * Returns an array used to specify which select option is chosen for the review rating on the 'quick edit' panel
     * for reviews.
     *
     * Example: $stars = would return $star_sel_arr['','',' selected','','']
     *
     * @param $stars
     * @return array
     */
    protected function set_star_sel($stars) {
        $star_sel_arr = array();
        for ($s = 1 ; $s <= 5 ; ++$s) {
            if ($s == $stars) {
                $star_sel_arr[$s] = ' selected';
            } else {
                $star_sel_arr[$s] = '';
            }
        }
        return $star_sel_arr;
    }

    /**
     * Returns either $pos or $neg arguments depending on boolean value of $hidden.
     *
     * @param $hidden integer
     * @param $pos
     * @param $neg
     * @return mixed
     */
    protected function set_hidden($hidden, $pos, $neg) {
        switch ($hidden) {
            case 1:
                return $pos;
            default:
                return $neg;
        }
    }

    /**
     * Converts &lt;br&gt; to new new line for display inside a text area element.
     *
     * @param $cont string Review text content from reviews
     * @return string
     */
    protected function set_content($cont) {
        return str_replace('<br>', '&#13;&#10;', rtrim($cont, '<br>'));
    }

    /**
     * Returns formatted date based on $this->sql_date_format.
     *
     * @param $date
     * @return false|string
     */
    protected function set_date($date) {
        return date($this->sql_date_format, strtotime($date));
    }

    /**
     * Checks if a row is odd. If so, return 'tr_white' (css class used to make a table row white).
     *
     * @param $row_num integer
     * @return string
     */
    protected function check_odd($row_num) {
        if ($row_num % 2 == 0) {
            // it's odd.
            return ' tr_white';
        } else {
            return '';
        }
    }

    public function return_table()
    {
        return $this->table;
    }
}

// $build_table = new review_admin_table();
// echo $build_table->return_table();

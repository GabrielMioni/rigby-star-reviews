<?php

require_once('build_abstract.php');
require_once('check_admin.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Builds a HTML table of review results used for the admin panel found at star-reviews/review_admin/reviews.php
 *
 * The review table displays review data and also provides an interface to do 'quick' and 'detailed' edits for reviews.
 *
 * This class is called through class search_reviews which is responsible for getting field values from the review search form.
 * 1. search_reviews class builds $_GET values from text inputs. All null values returns all reviews.
 * 2. build_review_table builds a prepared statement. PDO statement is found in abst_review_select
 * 3. build_review_table builds an HTML table.
 *
 */
class build_reviews_table extends build_abstract {
    
    use check_admin;

    /**
     * Holds review data from prepared statement in abst_review_select.php based on constructor arguments.
     *
     * @var array
     */
    protected $review_array = array();

    /**
     * Check to see if username is valid
     *
     * @var bool
     */
    protected $admin_un;

    /**
     * Check to see if password is valid
     *
     * @var bool
     */
    protected $admin_pw;

    /**
     * Check to see if user as privileges to edit review data.
     *
     * If the current user doesn't have admin privileges, the HTML for the review table will not include options to
     * edit.
     *
     * Set by $this->priv_chk() found in the check_admin trait
     *
     * @var bool
     */
    protected $priv_bool;

    /**
     * Formatting for dates
     *
     * @var string
     */
    protected $sql_date_format;

    /**
     * String that holds $_GET values.
     *
     * This string is used to build URLs that make it possible for the user to return to the same search results they had been viewing
     * after making detailed edits on ../detail_edit.php
     *
     * @var string
     */
    protected $get_string;

    /**
     * HTML for the review table.
     *
     * @var string
     */
    protected $table;
    
    public function __construct($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array) {
        parent::__construct($title, $name, $email, $ip, $page, $page_p, $date_range, $date_single, $date_start, $date_end, $star_array);
        
        $this->admin_un = isset($_SESSION['username']) ? $_SESSION['username'] : false;
        $this->admin_pw = isset($_SESSION['password']) ? $_SESSION['password'] : false;
        
        $this->sql_date_format = 'Y-m-d H:i:s';

        $this->get_string   = $this->set_get_values();
        $this->priv_bool    = $this->priv_chk($this->admin_un, $this->admin_pw);
        $this->review_array = $this->run_get_reviews();
        
        if (is_array($this->review_array)) {
            $this->table = $this->build_table($this->review_array, $this->get_string);
        } else {
            $this->table = "<div id='table_probs'>There was a problem fetching data. Please try again later.</div>";
        }

    }

    /**
     * Checks to see if the current user has admin privileges. Uses the username/password submitted
     * at login.
     *
     * @param $admin_un
     * @param $admin_pw
     * @return bool
     */
    protected function priv_chk($admin_un, $admin_pw) {
        $chk_user = $this->chk_admin_creds($admin_un, $admin_pw);
//        $chk_user = $this->chk_admin_creds($this->admin_un, $this->admin_pw);
        switch ($chk_user) {
            case TRUE:
                $chk_priv = $this->chck_admin_priv($this->admin_un);
                return $chk_priv;
            default:
                return FALSE;
        }
    }

    /**
     * Build a string based on $_GET values.
     *
     * @return string
     * @todo add a check to make sure $_GET values are valid indexes.
     */
    protected function set_get_values() {
        $get_string = '';
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                $get_string .= "$key=$value&";
            }
        }
        return rtrim($get_string, '&');
    }

    /**
     * Returns array if review data found. Returns -1 if no data is found.
     *
     * Calls method $this->build_review_select() found in /review_admin/php/abst_review_select.php
     *
     * @return string|integer
     * @todo change integer to boolean false.
     */
    protected function run_get_reviews() {
        return $this->build_review_select("*");
    }

    /**
     * Sets HTML for the review table.
     *
     * @param $review_array
     * @param $get_string
     * @return string
     */
    protected function build_table($review_array, $get_string) {
//        $review_array = $this->review_array;
//        $get_string = $this->get_string;

        $thead = "  <thead>
                        <tr class='head'>
                            <th id='col_id'>Id</th>
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
            $title  = $row['title'];
            $name   = $row['name'];
            $email  = $row['email'];
///            $prod   = $row['product'];
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
            
            
            $detail_edit_url = "detail_edit.php?id=$id&$get_string";
            // <a href='http://gabrielmioni.com/projects/star-reviews/review_admin/detail_edit.php?id=".$id."&$get_string'><div class='tog pencil'></div></a>
            if ($this->priv_bool == TRUE) {
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
            
            $row_edit = "<tr class='row_edit$odd_check'>
                            <td colspan='9'>
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
        
//        $this->table = $table;
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

    /**
     * Public access to $this->table.
     *
     * @return string
     */
    public function return_table() {
        return $this->table;
    }
    
}

/*
    function validateDate($date, $format = 'Y-m-d H:i:s') {
        // http://au1.php.net/checkdate#113205
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }*/
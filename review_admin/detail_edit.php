<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file displays a detailed view of review data for a single review. The review data is populated in an HTML form.
 * The data displayed can be updated and submitted by clicking the <input id='detail_submit> button.
 *
 * Form validation takes place in php/detail_edit.php [called in the HTML form action at php/detail_edit_act.php]. When
 * a review update is submitted, the Rigby use is show an appropriate 'success' message, or provided with errors telling
 * them which form inputs need to be corrected.
 *
 * Note: detail_edit.php is usually accessed by expanding a review from the review table found at reviews.php and clicking
 * on 'Go to detail edit.'
 */

/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once('../config.php');

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');
require_once('php/set_input_data.php');
require_once('php/build_return_url.php');

/* ***********************************************************************************
 * $_SESSION array used to hold Rigby user credentials, error messages and input values.
 * ***********************************************************************************/
session_start();

/* *************************************************************
 * Confirm the Rigby user is logged in with correct credentials.
 * - class login_check found in ../review_login/login_check.php
 * *************************************************************/
new login_check();

/* **************************************
 * HTML for top and left navigation bars.
 * - class admin_bars found at php/admin_bars.php
 * **************************************/
$bars = new admin_bars(__FILE__);
$sidebar = $bars->return_sidebar();
$toolbar = $bars->return_toolbar();


/* **************************************************************
 * If $_GET['id'] is not set, set $id to be a non-numeric value.
 * ***************************************************************/
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = 'x';
}

/* ****************************************************************************************************
 * Get review data using the review id specified by $_GET['id'].
 * - If no review is found, $review_data will be an empty array. Else, $review_data will include array
 * elements from the review record that's found.
 * ****************************************************************************************************/
$get_input_data = new set_input_data();
$review_data = $get_input_data->return_input_array();

/*  If no review data is found using the $_GET['id'] value ($review_data is empty) or if $_GET['id'] is not
    a number, return to reviews.php  */

/* *******************************************************************************************
 * If either $id is non-numeric, or $review_data is empty, send Rigby user back to reviews.php
 * *******************************************************************************************/
if (empty($review_data) || (!is_numeric($id))) {
    header('Location: reviews.php');
}

/* ************************************************************************************
 * Set input variables. These are displayed in the HTML fomr found in detail_edit.php
 * ************************************************************************************/
$input_id     = $review_data['id'];
$input_title  = $review_data['title'];
$input_name   = $review_data['name'];
$input_email  = $review_data['email'];
$input_prod   = $review_data['product'];
$input_cont   = $review_data['cont'];
$input_reply  = $review_data['reply'];
$input_ip     = $review_data['ip'];
$input_hidden = $review_data['hidden'];
$input_date   = $review_data['date'];
$input_stars  = $review_data['stars'];

/* ***************************************************************************
 * These values represent the drop down options for the #stars select element.
 *  - each variable initialized as whitespace.
 *  - set_select_val() sets the appropriate variable to 'selected'.
 * ***************************************************************************/
$sel_1 = '';
$sel_2 = '';
$sel_3 = '';
$sel_4 = '';
$sel_5 = '';

/**
 * Sets which variable is 'selected.' The appropriate variable is passed 'selected' value
 * by reference.
 *
 * @param $star_input int The 1-5 value of the star review being edited. Set by $review_data['stars']
 * @param $sel_1 string whitespace
 * @param $sel_2 string whitespace
 * @param $sel_3 string whitespace
 * @param $sel_4 string whitespace
 * @param $sel_5 string whitespace
 */
function set_select_val($star_input, &$sel_1, &$sel_2, &$sel_3, &$sel_4, &$sel_5) {
    $selected = ' selected';
    switch ($star_input) {
        case 1:
            $sel_1 .= $selected;
            break;
        case 2:
            $sel_2 .= $selected;
            break;
        case 3:
            $sel_3 .= $selected;
            break;
        case 4:
            $sel_4 .= $selected;
            break;
        case 5:
            $sel_5 .= $selected;
            break;
        default:
            $sel_5 .= $selected;
            break;
    }
}

set_select_val($input_stars, $sel_1, $sel_2, $sel_3, $sel_4, $sel_5);


/* ************************************************************************************
 * $sel_no and $sel_yes represent radio buttons for #hid_no and #hid_yes
 *  - If the review is hidden, $sel_yes is set to 'checked'. Else, $sel_no is set to checked.
 * ************************************************************************************/
$sel_no = '';
$sel_yes = '';

if ($input_hidden == 0) {
    $sel_no  = ' checked';
    $sel_yes = '';
}
if ($input_hidden == 1) {
    $sel_no  = '';
    $sel_yes = ' checked';
}

/* ************************************************************************************
 * $sel_no and $sel_yes represent radio buttons for #hid_no and #hid_yes
 *  - If the review is hidden, $sel_yes is set to 'checked'. Else, $sel_no is set to checked.
 * ************************************************************************************/
$build_return_url = new build_return_url($input_id);
$return_url = $build_return_url->return_url();

/**
 * Creates a string from $_GET values that are set. The string is passed to the HTML form on detail_edit.php as a
 * hidden input value.
 *
 * This lets the edit_detail class [called by the HTML form action at ../edit_detail_act.php] build a return header
 * using detail_edit::set_header() [which sends the Rigby user back to detail_edit.php with a success or failures message.].
 *
 * @return string string with URL encoded $_GET values.
 */
function set_get_vars() {
    $get_vars = '';
    if (isset($_GET)) {
        foreach ($_GET as $key => $value) {
            $get_vars .= "$key=$value&";
        }
    }
    $set_get = rtrim($get_vars, '&');
    return $set_get;
}

// Used in the HTML form at <input id='get_vars'>
$get_vars = set_get_vars();


/**
 * Collects error messages from $_SESSION into a new array. This makes it easier to call display error messages in the
 * HTML. Also assures array keys are always valid.
 *
 * @return array Contains validation errors for the form on detail_edit.php
 */
function set_error_from_sess() {
    $tmp = array();
    $empty = '';
    $tmp['err_id']     = $empty;
    $tmp['err_name']   = $empty;
    $tmp['err_title']  = $empty;
    $tmp['err_email']  = $empty;
    $tmp['err_ip']     = $empty;
    $tmp['err_date']   = $empty;
    $tmp['err_hidden'] = $empty;
    $tmp['err_cont']   = $empty;    
    
    if (isset($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $chk = strpos($key, '_prob');
            if ($chk == true) {
                $err_type = str_replace('_prob', '', $key);
                $var_name = 'err_'.$err_type;
                $tmp[$var_name] = $value;
            }
        }
    }
    return $tmp;
}

/* *********************************************************************************
 * Initialize error variables.
 * - error variables are displayed in <div class='err'> elements within the HTML form.
 * *********************************************************************************/
$set_errs = set_error_from_sess();

foreach ($set_errs as $key => $value) {
    ${$key} = $value;
}

/* ********************************************************
 * Set HTML Success Message
 *  - The $success variable is echoed in <div id='success'>
 * ********************************************************/
if (isset($_SESSION['success'])) {
    $success = "Record is updated!";
} else {
    $success = '';
}

/**
 * Unsets $_SESSION variables with a key that includes the function argument $type.
 *
 * Used to clear error messages so they don't remain on page reload.
 *
 * @param $type string Function looks for the $type string in the $_SESSION key. If it finds it,
 * the $_SESSION element will be unset.
 */
function clear_sess_type($type) {
    if (isset($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $chk = strpos($key, $type);
            if ($chk !== FALSE) {
                unset($_SESSION[$key]);
            }
        }
    }
}

clear_sess_type('_prob');

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Reviews - Detail Edit</title>
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="css/detail_edit.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <?php
        echo $toolbar;
        ?>
        <div id='edit_wrap'>
            <?php
            echo $sidebar;
            ?>
            <div id='hud'>
                <div id="hud_cont">
                    <form id='detail_form' action='php/edit_detail_act.php' method='post'>
                        <h3>Detail Review Edit:</h3>
                        <div class='left'>
                            <input id='id' name='id' type='hidden' value='<?php echo $input_id; ?>'>
                            <div class='row'>
                                <label for="title">Title:</label><div class='err'><?php echo $err_title; ?></div><br>
                                <input id='title' name="title" type="text" value="<?php echo $input_title; ?>">
                            </div>
                            <div class='row'>
                                <label for="name">Name:</label><div class='err'><?php echo $err_name; ?></div><br>
                                <input id='name' name="name" type="text" value="<?php echo $input_name; ?>">
                            </div>
                            <div class='row'>
                                <label for="email">Email:</label><div class='err'><?php echo $err_email; ?></div><br>
                                <input id='email' name="email" type="text" value="<?php echo $input_email; ?>">
                            </div>
                            <div class='row'>
                                <label for="ip">IP:</label><div class='err'><?php echo $err_ip; ?></div><br>
                                <input id='ip' name="ip" type="text" value="<?php echo $input_ip; ?>">
                            </div>
                            <div class='row'>
                                <label for="date">Date:</label><div class='err'><?php echo $err_date; ?></div><br>
                                <input id='date' name="date" type="text" value="<?php echo $input_date; ?>">
                            </div>
                            <div class='sel_row'>
                                <span class='psuedo_label'>Hidden:</span><div class='err'><?php echo $err_hidden; ?></div>
                                <label for='hid_yes'>Yes:</label> <input id='hid_yes' type="radio" name="hidden" value="1"<?php echo $sel_yes; ?>>
                                <label for='hid_no'>No:</label> <input id='hid_no' type="radio" name="hidden" value="0"<?php echo $sel_no; ?>>
                            </div>
                            <div class='sel_row'>
                                <label for="stars">Stars:</label><div class='err'></div>
                                <select id='stars' name="stars">
                                    <option value="5"<?php echo $sel_5; ?>>★★★★★</option>
                                    <option value="4"<?php echo $sel_4; ?>>★★★★ </option>
                                    <option value="3"<?php echo $sel_3; ?>>★★★  </option>
                                    <option value="2"<?php echo $sel_2; ?>>★★   </option>
                                    <option value="1"<?php echo $sel_1; ?>>★    </option>
                                </select>
                            </div>                            
                        </div>
                        <div class='right'>
                            <label for='cont'>Review:</label>
                            <textarea name='cont' id='cont'><?php echo $input_cont;?></textarea>
                            <label for='cont'>Reply:</label>
                            <textarea name='reply' id='reply'><?php echo $input_reply; ?></textarea>
                            <div id="success"><?php echo $success; ?></div>
                            <input type="hidden" name='get_vars' id="get_vars" value="<?php echo $get_vars; ?>">
                            <input type="submit" id='detail_submit' value="Update">
                            <div class='row'>
                                <?php                                 
                                echo "<a href='$return_url'>Go back to the reviews page.</a>";
                                clear_sess_type('post_');
                                clear_sess_type('success');
                                ?>                                
                            </div>
                        </div>
                    </form>
                </div>                
            </div>
        </div>
    </body>
</html>


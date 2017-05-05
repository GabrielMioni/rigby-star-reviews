<?php

/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once('../config.php');

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');

/* ********************************************************************************************
 * search_reviews.php and search_pagination.php:
 * -display results from the review search form at <div id='review_search' class="box_cont">.
 * ********************************************************************************************/
require_once('php/search_reviews.php');
require_once('php/search_pagination.php');

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

/* *************************************************************************************
 * Display results from search criteria [set as $_GET values by the HTML search form].
 * - If no search criteria is set, default display is 10 results.
 * *************************************************************************************/
$search_review_table = new search_reviews();
$review_table = $search_review_table->return_table();

/* *******************************************************************
 * Display pagination bar to allow the Rigby user to traverse reviews.
 * *******************************************************************/
$search_pagination = new search_pagination();
$pagination_bar = $search_pagination->return_pagination();


/**
 * Sets variables to be displayed in the review form's text input values.
 *
 *
 * @param $target   string Variable being set by $_GET[$key_name]
 * @param $key_name string Key name for the $_GET element requested.
 */
function set_get_val(&$target, $key_name) {
    $target = isset($_GET[$key_name]) ? urldecode($_GET[$key_name]) : '';
}

set_get_val($title_val, 'title_search');
set_get_val($name_val, 'name_search');
set_get_val($email_val, 'email_search');
set_get_val($ip_val, 'ip_search');
set_get_val($date_single_val, 'date_single');
set_get_val($date_start_val, 'date_start');
set_get_val($date_end_val, 'date_end');
set_get_val($page_val, 'page');
set_get_val($page_p, 'page_p');

/**
 * Sets variables to ' checked' for checkboxes in the <div id='star_search'> element.
 *
 * @param $target   string Variable being set by $_GET[$key_name]
 * @param $key_name string Key name for the $_GET element requested.
 */
function set_checked_val(&$target, $key_name) {
    $target = isset($_GET[$key_name]) ? ' checked' : '';
}

set_checked_val($star_1_val, 'star-1');
set_checked_val($star_2_val, 'star-2');
set_checked_val($star_3_val, 'star-3');
set_checked_val($star_4_val, 'star-4');
set_checked_val($star_5_val, 'star-5');

/**
 * Sets variables used to set the drop down menu for how many reviews to display in results.
 *
 * @param $sel_10   string whitespace
 * @param $sel_20   string whitespace
 * @param $sel_50   string whitespace
 * @param $sel_100  string whitespace
 * @param $sel_1000 string whitespace
 */
function set_select_val(&$sel_10, &$sel_20, &$sel_50, &$sel_100, &$sel_1000) {
    $selected = ' selected';
    $page_p = isset($_GET['page_p']) ? $_GET['page_p'] : '';
    if ($page_p !== '') {
        switch ($page_p) {
            case 10:
                $sel_10 = $selected;
                break;
            case 20:
                $sel_20 = $selected;
                break;
            case 50:
                $sel_50 = $selected;
                break;
            case 100:
                $sel_100 = $selected;
                break;
            case 1000:
                $sel_1000 = $selected;
                break;
            default:
                break;
        }
    }
}


$sel_10 = '';
$sel_20 = '';
$sel_50 = '';
$sel_100 = '';
$sel_1000 = '';

set_select_val($sel_10, $sel_20, $sel_50, $sel_100, $sel_1000);


?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Reviews</title>
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="css/reviews.css">
        <link rel='stylesheet' href='css/admin_tbl.css'>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald">
        <link rel='stylesheet' href='jqueryui/jquery-ui.css'>
        <link rel='stylesheet' href='jqueryui/jquery-ui.theme.css'>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src="script/reviews.js"></script>
        <script type="text/javascript" src='script/box_toggle.js'></script>
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
                <div id='hud_title'><h3>Reviews</h3></div>
                <div id='hud_cont'>
                    <div id='review_search' class="box">
                        <h3>Search</h3>
                        <div class="box_toggle">
                            <div class="tog toggle_up"></div>
                        </div>
                        <div id='review_search' class="box_cont">
                            <form id='search_form' action="" method="get">
                                <div class="left">
                                    <div class='search_row'>
                                        <label for='title_search'>Title:</label>
                                        <input name="title_search" id="title_search" type="text" value="<?php echo $title_val; ?>">
                                    </div>
                                    <div class="search_row">
                                        <label for="name_search">Name:</label>
                                        <input name="name_search" id="name_search" type="text" value="<?php echo $name_val; ?>">
                                    </div>
                                    <div class="search_row">
                                        <label for="email_search">Email:</label>
                                        <input name="email_search" id="email_search" type="text" value="<?php echo $email_val; ?>">
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="search_row">
                                        <label for="ip_search">IP Address:</label>
                                        <input name="ip_search" id="ip_search" type="text" value="<?php echo $ip_val; ?>">
                                    </div>
                                    <div class="date_row">
                                        <div class="date_radio">
                                            <span>
                                                <input id="date_single" type="radio" name="date_range" value="date_single"><label for="date_single">Date</label>
                                            </span>
                                            <span id="date_range_span">
                                                <input id="date_range" type="radio" name="date_range" value="date_range" checked="checked"><label for="date_range">Date Range</label>
                                            </span>
                                        </div>
                                        <div id="single_date_picker">
                                            <input id="single_date" name="date_single" type="text" value="<?php echo $date_single_val; ?>">
                                        </div>                                        
                                        <div id="date_range_wrap">
                                            <div>
                                                <input id="date_start" class="date_range_txt" name="date_start" type="text" value="<?php echo $date_start_val; ?>">
                                            </div>
                                            <div>
                                                <input id="date_end" class="date_range_txt" name="date_end" type="text" value="<?php echo $date_end_val; ?>">
                                            </div>
                                            
                                        </div>

                                    </div>
                                    <div class='star_row'>
                                        <label>Stars:</label>
                                        <div id='star_search'>
                                            <input type="checkbox" name="star-1" id="star-1"<?php echo $star_1_val; ?>>
                                            <label for="star-1">1 Star</label>
                                            <input type="checkbox" name="star-2" id="star-2"<?php echo $star_2_val; ?>>
                                            <label for="star-2">2 Star</label>
                                            <input type="checkbox" name="star-3" id="star-3"<?php echo $star_3_val; ?>>
                                            <label for="star-3">3 Star</label>
                                            <input type="checkbox" name="star-4" id="star-4"<?php echo $star_4_val; ?>>
                                            <label for="star-4">4 Star</label>
                                            <input type="checkbox" name="star-5" id="star-5"<?php echo $star_5_val; ?>>
                                            <label for="star-5">5 Star</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="search_row">
                                    <select name='page_p'>
                                        <option value="10"<?php echo $sel_10; ?>>10</option>
                                        <option value="20"<?php echo $sel_20; ?>>20</option>
                                        <option value="50"<?php echo $sel_50; ?>>50</option>
                                        <option value="100"<?php echo $sel_100; ?>>100</option>
                                        <option value="1000"<?php echo $sel_1000; ?>>1000</option>
                                    </select>
                                    <input name='search' type='submit' value="Search">
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    echo $pagination_bar;
                    echo $review_table;
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>

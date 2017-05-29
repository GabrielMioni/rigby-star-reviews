<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once('../rigby_root.php');

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');

/* ********************************************************************************************
 * search_reviews.php and search_pagination.php:
 * -display results from the review search form at <div id='review_search' class="box_cont">.
 * ********************************************************************************************/
require_once('php/product_table.php');
require_once('php/products_pagination.php');
// require_once('php/search_reviews.php');
//require_once('php/search_pagination.php');

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

function check_get_value($get_index) {
    if (isset($_GET[$get_index])) {
        return htmlspecialchars($_GET[$get_index]);
    } else {
        return '';
    }
}

$product_name   = check_get_value('product_name');
$product_id     = check_get_value('product_id');
$date_set       = check_get_value('date_set');
$date_start     = check_get_value('date_start');
$date_end       = check_get_value('date_end');
$results_per_page = 10;

if (isset($_GET['page']))
{
    $page = $_GET['page'];
} else {
    $page = 1;
}

$success_message = '';
if (isset($_SESSION['success_message']))
{
    $success_message .= $_SESSION['success_message'];

    $success_message = "<div class='success'>$success_message</div>";
}

$failure_message = '';
if (isset($_SESSION['failure_message']))
{
    $failure_message .= $_SESSION['failure_message'];

    $failure_message = "<div class='error'>$failure_message</div>";
}

$build_table = new product_table($product_name, $product_id, $date_set, $date_start, $date_end, $page, $results_per_page);
$table = $build_table->return_table();

$build_pagination = new products_pagination($page, 10, 10);
$pagination_bar = $build_pagination->get_pagination_bar();

/**
 * Sets variables to be displayed in the review form's text input values.
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
    <link rel="stylesheet" href="css/products.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald">
    <link rel='stylesheet' href='jqueryui/jquery-ui.css'>
    <link rel='stylesheet' href='jqueryui/jquery-ui.theme.css'>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="script/reviews.js"></script>
    <script type="text/javascript" src='script/box_toggle.js'></script>
    <script type="text/javascript" src='script/products.js'></script>
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
        <div id='hud_title'><h3>Products</h3></div>
        <div id='hud_cont'>
            <div id='search' class="box">
                <h3>Search</h3>
                <div class="box_toggle">
                    <div class="tog toggle_up"></div>
                </div>
                <div id='product_search' class="box_cont">
                    <form id='search_form' action="" method="get">
                        <div class="search_row">
                            <div class="product_name_search">
                                <label class='product_label' for="product_name">Product name:</label>
                                <input type="text" name="product_name" value="<?php echo $product_name; ?>">
                            </div>
                            <div class="product_id_search">
                                <label class='product_label' for="product_id">Product Id:</label>
                                <input type="text" name="product_id" value="<?php echo $product_id; ?>">
                            </div>
                        </div>
                        <div class="search_row">
                            <div class="date_select_wrapper">
                                <div class="date_type_select">
                                    <div class="date_rad">
                                        <input name="date_range" value="0" type="radio"><label for="date_single">Date</label>
                                    </div>
                                    <div class="date_rad">
                                        <input name="date_range" value="1" type="radio" checked="checked"><label for="date_range">Date Range</label>
                                    </div>
                                </div>
                                <div class="product_date_start">
                                    <input type="text" name="date_start" value="<?php echo $date_start; ?>">
                                </div>
                                <div class="product_date_end">
                                    <input type="text" name="date_end" value="<?php echo $date_end; ?>">
                                </div>
                                <div class="product_date_set">
                                    <input type="text" name="date_set" value="<?php echo $date_set; ?>">
                                </div>
                            </div>
                        </div>
                        <input type="submit" value="Search Products">
                    </form>
                </div>
            </div>
            <?php
            echo $pagination_bar;
            ?>
            <div id="response">
                <?php
                echo $failure_message;
                echo $success_message;
                ?>
            </div>
            <?php
            echo $table;

            if (isset($_SESSION['failure_message']))
            {
                unset($_SESSION['failure_message']);
            }
            if (isset($_SESSION['success_message']))
            {
                unset($_SESSION['success_message']);
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
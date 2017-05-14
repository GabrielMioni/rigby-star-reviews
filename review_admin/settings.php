<?php
session_start();
/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once('../rigby_root.php');

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');

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

if (isset($_SESSION['fake_reviews_msg']))
{
    $fake_review_msg = $_SESSION['fake_reviews_msg'];
} else {
    $fake_review_msg = '';
}


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
    <style>
        #fake_review_console {
            width: 400px;
        }
        #fake_review_console form {
            padding: 10px;
            width: 300px;
        }
        #fake_review_console .row {
            display: block;
            overflow: hidden;
            position: relative;
        }
        #fake_review_console label {
            float: left;
        }
        #fake_review_console input[type="text"] {
            float: right;
        }
        #fake_review_console input[type="submit"] {
            background-color: #4fc3f7;
            border: 1px solid #4fc3f7;
            color: white;
            cursor: pointer;
            font-size: 17px;
            min-width: 100%;
            padding: 5px;
            text-decoration: none;
            transition: all .5s;
            text-shadow: -1px 0 #03a9f4, 0 1px #03a9f4, 1px 0 #03a9f4, 0 -1px #03a9f4;
        }

        #fake_review_console input[type="submit"]:hover {
            background-color: #03a9f4;
            border: 1px solid #03a9f4;
        }

        #search_form > #fake_review_msg {
            min-height: 25px;
        }




    </style>
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
            <div id='hud_title'><h3>Settings</h3></div>
            <div id='hud_cont'>
                <div id='review_search' class="box">
                    <h3>Create Fake Reviews:</h3>
                    <div class="box_toggle">
                        <div class="tog toggle_up"></div>
                    </div>
                    <div id='fake_review_console' class="box_cont">
                        <form id='search_form' action="php/fake_reviews_create.php" method="post">
                            <div class='row'>
                                <label for='fake_review_count'># of reviews to create:</label>
                                <input name="fake_review_count" type="text" value="">
                            </div>
                            <div class="row">
                                <label for="date_start">Date Start:</label>
                                <input name="date_start" type="text" value="">
                            </div>
                            <div class="row">
                                <label for="date_end">Date End:</label>
                                <input name="date_end"  type="text" value="">
                            </div>
                            <div id="fake_review_msg">
                                <?php
                                echo $fake_review_msg;
                                if(isset($_SESSION['fake_reviews_msg']))
                                {
                                    unset($_SESSION['fake_reviews_msg']);
                                }
                                ?>
                            </div>
                            <input type="submit" value="Create Fake Reviews">
                        </form>
                    </div>
                </div>
            </div>
        </div>

</body>
</html>

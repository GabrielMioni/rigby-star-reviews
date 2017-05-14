<?php

/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once("../rigby_root.php");

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');

require_once('php/build_users_table.php');
require_once('php/check_admin.php');

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

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Rigby Reviews</title>
        <link rel='stylesheet' href='css/admin_tbl.css'>
        <link rel='stylesheet' href='css/users.css'>
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel='stylesheet' href='jqueryui/jquery-ui.css'>
        <link rel='stylesheet' href='jqueryui/jquery-ui.theme.css'>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src='script/users.js'></script>
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
                <div id='user_wrap'>
                    <div id='add_user'>
                        <i class='fa fa-plus' aria-hidden='true'></i>
                        <p>Add User</p>
                        <a href='add_user.php'></a>
                    </div>
                    <div id='users_table' class="box">
                        <?php
                            $build_table = new build_users_table();
                            $table = $build_table->return_table();

                            echo $table;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div id='canvas'>
            <div id='add_user_form'>
                <form class='form_thing' action='php/add_user_act.php' method='post'>
                    <div class='form_row'>
                        <label for='new_name'>Name:</label>
                        <div class="error error_name"></div>
                        <input id='new_name' name='new_name' type='text'>
                    </div>
                    <div class='form_row'>
                        <label for='new_email'>Email:</label>
                        <div class="error error_email"></div>
                        <input id='new_email' name='new_email' type='text'>
                    </div>
                    <div class='form_row'>
                        <label for='pswd_set'>Password:</label>
                        <div class="error error_password"></div>
                        <input id='pswd_set' name='pswd_set' type='text'>
                    </div>
                    <div class='form_row'>
                        <label for='pswd_con'>Confirm Password:</label>
                        <div class="error error_confirm"></div>
                        <input id='pswd_con' name='pswd_con' type='text'>
                    </div>
                    <div class='sel_row'>
                        <span class='psuedo_label'>Privileges:</span><div class='error error_priv'></div>
                        <div class="error"></div>
                        <label for='priv_admin'>Admin:</label> <input id='priv_admin' name='priv' value='1' type='radio'>
                        <label for='priv_team'>Team:</label> <input id='priv_team' name='priv' value='0' checked='' type='radio'>
                    </div>
                    <div class='form_row'>
                        <input id='submit_user' value='Add User' type='submit'>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>

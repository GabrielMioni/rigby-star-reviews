<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file displays quick info about the review database in a dashboard format.
 *
 * This includes:
 * 'At A Glance' - Review totals [all time, today and yesterday] and review average.
 * 'Recent Activity' - The last five reviews with short info [reviewer's name/email, rating and date].
 * 'Weekly Report' - Bar graph displaying total reviews for each day of the current week.
 *
 * HTML for each dashboard element is created by calling call objects that share a parent abstract class
 * [php/dash_abstract.php].
 *
 */

/* ********************************************************************
 * Define RIGBY_ROOT
 * - Any classes calling the sql_pdo class will fail without RIGBY_ROOT
 * ********************************************************************/
require_once('../config.php');

require_once(RIGBY_ROOT . '/review_login/login_check.php');
require_once('php/admin_bars.php');

/* ********************************************************************
 * Include classes that create dashboard elements.
 * - These classes are called in the HTML for clarity.
 * ********************************************************************/
require_once('php/dash_glance.php');
require_once('php/dash_bars.php');
require_once('php/dash_activity.php');

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
        <title>Rigby Dashboard</title>
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
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
                <div id='hud_title'><h3>Dashboard</h3></div>
                <div id='hud_cont'>
                    <div id='left'>
                        <?php
                        $date = date('m/d/y', time());
                        $build_dash = new dash_glance($date);
                        $dash_glace = $build_dash->return_widget();
                        echo $dash_glace;

                        $build_bar = new dash_bars($date);
                        $graph = $build_bar->return_widget();
                        echo $graph;
                        ?>
                    </div>                    
                    <div id='right'>
                        <?php
                        $build_activity = new dash_activity(1);
                        $activity = $build_activity->return_widget();
                        echo $activity;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
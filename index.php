<?php
session_set_cookie_params(0);
session_start();
require_once('rigby_root.php');
require_once('widgets/submit_module.php');
require_once('widgets/aggregate_rating.php');
require_once('widgets/histogram.php');
require_once('widgets/sidebar.php');
require_once('widgets/paginator_public.php');


$current_year = date('Y', time());
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Gabriel Mioni - Web Developer</title>
        <link href="../../css/main.css" rel="stylesheet">
        <link rel="stylesheet" href="css/rigby.css">
        <link href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link rel='stylesheet' href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    </head>
    <body>
        <div id='content'>
            <div id='left'>
                <?php
                $build_submit_module = new submit_module();
                $submit_module = $build_submit_module->return_submit_module();

                echo $submit_module;

                $aggregate_settings = array();
                $aggregate_settings['product_name'] = 'Rigby';
                $aggregate_settings['price'] = '100';
                $build_aggregate = new aggregate_rating($aggregate_settings);
                echo $build_aggregate->return_aggregate();
                ?>
            </div>
            <div id='right'>
                <?php

                $build_histogram = new histogram('');
                echo $build_histogram->return_histogram();

                /* Configuration variables for sidebar and paginator classes
                $page = '';
                $reviews_per_page = '';
                $rating = '';
                $buttons_per_page = 10;
                $product_id = '';
                */

                $sidebar = new sidebar();
                echo $sidebar->return_sidebar();

                $paginator = new paginator_public();
                echo $paginator->return_pagination();



                ?>
            </div>
        </div>
        <div id='footer'>
            <div id="foot_copy">
                &copy; <?php echo $current_year; ?> Gabriel Mioni
            </div>
        </div>
        <script type='text/javascript' src='../../scripts/js/main.js'></script>
        <!--<script type='text/javascript' src='js/review_stars.js'></script>-->
        <!--<script type='text/javascript' src='js/review_navigation.js'></script>-->
        <!--<script type='text/javascript' src='js/js_fom.js'></script>-->
    </body>
    
</html>
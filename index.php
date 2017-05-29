<?php
session_set_cookie_params(0);
session_start();
require_once('rigby_root.php');
require_once('php/product_array.php');
require_once('php/build_review_form.php');

/*
$build_header = new page_header2('projects.php');
$header = $build_header->return_header();
*/

$build_review_form = new build_review_form($product_array);
$review_form = $build_review_form->return_review_form();

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
                <?php echo $review_form; ?>
            </div>
            <div id='right'>
                <?php
                require_once('php/review_widget.php');

                $build_widget = new review_widget('');
                echo $build_widget->return_widget();
                ?>
                <div id='review_col'>
                    <?php
                    require_once('php/sidebar.php');
                    require_once('php/paginator_reviews.php');

                    /* Configuration variables for sidebar and paginator classes */
                    $page = '';
                    $rating = '';
                    $reviews_per_page = '';
                    $buttons_per_page = 10;
                    $product = '';

                    $build_sidebar = new sidebar($page, $rating, $reviews_per_page, $product, $product_array);
                    $sidebar = $build_sidebar->getSidebarHtml();
                    echo $sidebar;
                    ?>
                </div>
                <?php
                $build_pagination = new paginator_reviews($page, $reviews_per_page, $buttons_per_page, $rating);
                $bar = $build_pagination->get_pagination_bar();
                echo $bar;
                /* Experiment
                    $build_pagination = new paginator($page, $rating, $reviews_per_page, $buttons_per_page);
                    $bar = $build_pagination->get_pagination_bar();
                    echo $bar;
                */
                ?>
            </div>
        </div>
        <div id='footer'>
            <div id="foot_copy">
                &copy; <?php echo $current_year; session_destroy(); ?> Gabriel Mioni
            </div>
        </div>
        <script type='text/javascript' src='../../scripts/js/main.js'></script>
        <script type='text/javascript' src='js/review_stars.js'></script>
        <script type='text/javascript' src='js/review_navigation.js'></script>
        <script type='text/javascript' src='js/js_fom.js'></script>
    </body>
    
</html>
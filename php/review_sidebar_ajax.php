<?php
require_once('review_sidebar.php');
require_once('product_array.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 * 
 * This file is used to return an HTML sidebar when navigationg through different
 * pages of review results.
 * 
 * It is called from the JS function get_reviews() found in js/reviews.js
 */

/*
if (isset($_GET['ajax'])) {
    if (isset($_GET['rating'])) {
        $rating = $_GET['rating'];
    } else {
        $rating = '';
    }
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        
        $set_page = $page-1;
        $build_sidebar = new review_sidebar($rating, '', $set_page, '8', $product_array);
        echo $build_sidebar->return_sidebar();
    }
}

*/
if (isset($_POST['ajax'])) {
    if (isset($_POST['rating'])) {
        $rating = $_POST['rating'];
    } else {
        $rating = '';
    }
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        $set_page = $page-1;
        $build_sidebar = new review_sidebar($rating, '', $set_page, '8', $product_array);
        echo $build_sidebar->return_sidebar();
    } else {
        echo "Nope";
    }
}
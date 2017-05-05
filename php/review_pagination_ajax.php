<?php

require_once('review_pagination.php');

/**
 * @package    Rigby
 * @author     Gabriel Mioni <gabriel@gabrielmioni.com>
 * 
 * This file is used to return pagination html from Ajax calls
 * 
 * It is called from the JS function get_pagination_bar() found in
 * js/reviews.js
 */

if (isset($_GET['ajax'])) {
    if (isset($_GET['rating'])) {
        if ($_GET['rating'] === null) {
            $set_rating = '';
        } else {
            $set_rating = $_GET['rating'];
        }
    } else {
        $set_rating = '';
    }
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        
        $build_sidebar = new review_pagination($page, $page, 8, $set_rating);
        echo $build_sidebar->return_pagination_bar();
    }
}
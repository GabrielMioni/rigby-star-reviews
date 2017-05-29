<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file builds a paginator_reviews bar for the Ajax call requested from ../index.php
 */

require_once('../rigby_root.php');
require_once('paginator_reviews.php');

if (isset($_POST['ajax']))
{
    $page       = isset($_POST['page']) ? $_POST['page'] : '';
    $rating     = isset($_POST['rating']) ? $_POST['rating'] : '';
    $reviews_per_page = '';
    $paginator = new paginator_reviews($page, $reviews_per_page, 10, $rating);
    $bar = $paginator->get_pagination_bar();
    $bar = str_replace('<div id="pagination_bar">', '', $bar);
    $bar = str_replace('</div>', '', $bar);
    echo $bar;

} else {
    echo FALSE;
}
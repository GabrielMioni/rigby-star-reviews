<?php
require_once('../config.php');
require_once('paginator.php');

if (isset($_POST['ajax']))
{
    $page       = isset($_POST['page']) ? $_POST['page'] : '';
    $rating     = isset($_POST['rating']) ? $_POST['rating'] : '';
    $reviews_per_page = '';
    $paginator  = new paginator($page, $rating, $reviews_per_page, 10, 'index.php');
    $bar = $paginator->get_pagination_bar();
    $bar = str_replace('<div id="pagination_bar">', '', $bar);
    $bar = str_replace('</div>', '', $bar);
    echo $bar;

} else {
    echo FALSE;
}
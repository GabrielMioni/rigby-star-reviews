<?php
require_once('../config.php');
require_once('sidebar.php');
require_once('product_array.php');

if (isset($_POST['ajax']))
{
    $page = isset($_POST['page']) ? $_POST['page'] : '';
    $rating = isset($_POST['rating']) ? $_POST['rating'] : '';
    $product = isset($_POST['product']) ? $_POST['produtct'] : '';

    $build_sidebar = new sidebar($page, $rating, '', $product, $product_array);
    $sidebar = $build_sidebar->getSidebarHtml();
    echo $sidebar;
}


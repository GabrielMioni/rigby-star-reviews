<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/products_array_set.php');

$product_array = product_array_set::get_product_array();

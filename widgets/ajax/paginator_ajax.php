<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/paginator_public.php');

if (isset($_POST['ajax']))
{
    $setting_array = array();
//    $setting_array['url'] = '';

    if (isset($_POST['page']))
    {
        $setting_array['page'] = $_POST['page'];
    }
    if ($_POST['rating'])
    {
        $setting_array['rating'] = $_POST['rating'];
    }
    if ($_POST['product'])
    {
        $setting_array['product_id'] = $_POST['product'];
    }

    $paginator = new paginator_public($setting_array);
    $bar = $paginator->return_pagination();
    $bar = str_replace('<div id="pagination_bar">', '', $bar);
    $bar = str_replace('</div>', '', $bar);
    echo $bar;
}
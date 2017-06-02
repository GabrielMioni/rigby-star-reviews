<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/sidebar.php');

if (isset($_POST['ajax']))
{
    $setting_array = array();

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

    $build_sidebar = new sidebar($setting_array);
    echo $build_sidebar->return_sidebar();
}
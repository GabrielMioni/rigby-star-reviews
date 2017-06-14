<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}

function set_rigby_home_url()
{
    $rigby_file = basename(RIGBY_ROOT);
    $protocol   = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

    $path = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
    $rigby_url = $protocol . $path;
    $rigby_url = substr($rigby_url, 0, strpos($rigby_url, $rigby_file)) . $rigby_file;

    return $rigby_url;
}

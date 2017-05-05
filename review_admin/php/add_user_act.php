<?php
require_once('add_user.php');

session_start();
$add_user = new add_user();
$ajax_reply = $add_user->get_ajax_reply();

echo $ajax_reply;

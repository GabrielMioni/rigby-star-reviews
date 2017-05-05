<?php
require_once('review_submit.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 * 
 * This file is meant to be the form action for the #review_form found
 * at index.php
 * 
 * The file is called either through direct form action, or an Ajax call
 * made when JavaScript is enabled and a user tries to submit the form.
 */

$submit_set = isset($_POST['submit']) ? true : false;
$ajax_set   = isset($_POST['ajax']) ? true : false;

if ($ajax_set == true)
{
    $ajax_submit = new review_submit(true);
    echo $ajax_submit->return_ajax_result();
    return;
}
if ($submit_set == true)
{
    new review_submit();
    return;
}

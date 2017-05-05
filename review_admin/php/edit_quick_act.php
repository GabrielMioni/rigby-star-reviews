<?php

require_once('edit_quick.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file is the form action for review update forms found in the ../reviews_php. It's called via Ajax.
 *
 */

/* ******************************************************************************************
 * 1. $_SESSION is used to validate the Rigby user's Username and Password.
 * ******************************************************************************************/
session_start();

/* ******************************************************************************************
 * 2. Start an edit_quick object.
 * ******************************************************************************************/
$edit = new edit_quick();

/* ******************************************************************************************
 * 3. Return a response letting the Ajax call know whether the update was successful or not.
 * - If successful, returns '1'.
 * - Else, returns a json encoded string with data about which form inputs failed validation.
 * ******************************************************************************************/
echo $edit->get_ajax_reply();
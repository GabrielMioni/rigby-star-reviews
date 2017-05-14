<?php
require_once('../../rigby_root.php');
require_once('edit_detail.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file is the form action for the detailed edit form found at ../detail_edit.php
 *
 */

/* ******************************************************************************************
 * 1. $_SESSION is used to validate the Rigby user's Username and Password.
 * ******************************************************************************************/
session_start();

/* ******************************************************************************************
 * 2. Start an edit_detail object.
 * - On success, $_SESSION is set with a success message.
 * - On failure, $_SESSION is set with info about which fields failed validation.
 * - Rigby user is re-directed to ../detail_edit.php
 * ******************************************************************************************/
new edit_detail();

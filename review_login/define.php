<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file defines SECRET_KEY used for generating a cookie to keep the Rigby user logged in for admin stuff.
 *
 * Used in:
 * - login_check.php
 * - login_remember.php
 *
 * It should be a 32 character hex string. Make sure to replace with something secure!
 */

define("SECRET_KEY", "32CHARHEXSTRINGDONTTELLANYONEOK?");
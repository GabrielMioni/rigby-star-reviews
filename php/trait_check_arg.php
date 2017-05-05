<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Trait check_arg holds a class method used to return a default value
 * in cases where a supplied variable is only whitespace.
 * 
 * Opted for a trait instead of an abstract class to improve readability and
 * because no parent constructor is necessary.
 * 
 * Used in:
 * - review_pagination.php
 * - review_return.php
 * 
 */
trait check_arg {
    /**
     * check_arg trait contains the check_arg method used in:
     * - review_insert.php
     *
     * If $arg is blank/whitesapce, returns $default. Else returns $arg. Used to
     * make sure empty values passed to the prepared sql statement are set to
     * a default value.
     *
     * @param string $arg
     * @param string $default
     *
     * @return check_arg string
     */
    protected function check_arg($arg, $default) {
        $return = trim($arg) === '' ? $default : $arg;
        return $return;
    }
}


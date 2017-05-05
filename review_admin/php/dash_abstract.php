<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * This abstract class contains some basic methods that will be
 * used whenever Rigby's Dashboard console needs to build a widget.
 * 
 * Dashboard console is found at review_admin/index.php
 * 
 * Used in:
 * - bar_glance.php
 * - dash_activity.php
 * - dash_glance.php
 *
 * @abstract
 */
abstract class dash_abstract {
    
    /**
     * Holds HTML for the widget being built.
     * 
     * Set by $this->build_widget().
     * Returned by $this->return_widget();
     * 
     * @access	protected
     * @var	string
     */
    protected $widget;
    
    /**
     * Defines common date formatting.
     * 
     * @access	protected
     * @var	string
     */
    protected $sql_date_format = 'Y-m-d H:i:s';

    /**
     * Runs prepared statement and returns result.
     *
     * @param $query string Prepared statement query.
     * @param array $pdo_array Array to pass to PDO.
     * @return bool|string If the query is successful, return result. Else return FALSE
     */
    protected function process_query_column($query, array $pdo_array) {
        try {
            $results = sql_pdo::run($query, $pdo_array)->fetchColumn();
        } catch (Exception $exc) {
            $results = FALSE;
            $this->problems['sql_err'] = $exc->getTraceAsString();
        }
        return $results;
    }

    /**
     * Concerete implentation responsible for building the
     * Dashboard widget and setting it at $this->widget
     * 
     * @access	protected
     * @abstract
     */
    abstract protected function build_widget();
    
    /**
     * Concerete implentation responsible for building the
     * Dashboard widget and setting it at $this->widget
     * 
     * @access protected
     * @return string Returns HTML from $this->widget
     */    
    public function return_widget() {
        return $this->widget;
    }
}

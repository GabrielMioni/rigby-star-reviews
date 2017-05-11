<?php
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Build an HTML table to display users for Rigby.
 *
 * Uses the check_admin trait to make sure the person submitting $_POST data has the credentials to allow admin
 * access.
 *
 * This table is found at star-reviews/review_admin/users.php
 *
 */
class build_users_table {
    protected $problems;
    protected $user_data;
    protected $table;

    public function __construct() {
        $this->user_data = $this->get_users_data();
        $this->table     = $this->build_table();
    }

    /**
     * Gets user data from the users.sql table.
     *
     * Tries to run prepared statement. If it fails, populates $this->problems.
     *
     * @return array|bool
     */
    protected function get_users_data() {
        $query = "SELECT * FROM users ORDER BY admin desc, username";

        try {
            $users = sql_pdo::run($query)->fetchAll();
        } catch (Exception $exc) {;
            $this->problems['user_data'] = 'There was a problem getting User data.';
            $users = false;
        }
        return $users;
    }

    /**
     * Returns HTML for the users table.
     *
     * @return string
     */
    protected function build_table() {
        $table = '<table id="review_admin">';
        $table .= $this->build_thead();
        $table .= $this->build_tbody();
        $table .= '</table>';

        return $table;
    }

    /**
     * Returns table head HTML.
     *
     * Used in $this->build_table()
     *
     * @return string
     */
    protected function build_thead() {
        $thead = '<thead>';
        $thead .=    '<tr class="head">';
        $thead .=        '<th>Username</th>';
        $thead .=        '<th>Email</th>';
        $thead .=        '<th>Created</th>';
        $thead .=        '<th>Privledges</th>';
        $thead .=        '<th>Locked</th>';
        $thead .=    '</tr>';
        $thead .= '</thead>';
        
        return $thead;
    }

    /**
     * Returns table body HTML.
     *
     * Used in $this->build_table().
     *
     * @return string
     */
    protected function build_tbody() {
        $user_data = $this->user_data;
        $tbody = '<tbody>';
        foreach ($user_data as $user) {
            $created_time = $user['reg_date'];

            if (strtotime($created_time) > time() - 10) {
                $just_added = " class = 'just_added'";
            } else {
                $just_added = '';
            }

            $tbody .= "<tr$just_added>";
            $tbody .= $this->format_user_row($user);
            $tbody .= '</tr>';
        }
        $tbody .= '</tbody>';
        return $tbody;
    }

    /**
     * Formats array rows from $this->user_data into HTML table rows.
     *
     * Called in a foreach loop in $this->build_tbody().
     *
     * @param $user
     * @return string
     */
    protected function format_user_row(array $user) {
        $username = $user['username'];
        $email    = $user['email'];
        $created  = $this->format_date($user['reg_date']);        
        $priv     = $this->return_bool_msg($user['admin'],  'Team', 'Admin');
        $locked   = $this->return_bool_msg($user['locked'], 'No',   'Yes');
        
        $row  = "<td>$username</td>";
        $row .= "<td>$email</td>";
        $row .= "<td>$created</td>";
        $row .= "<td>$priv</td>";
        $row .= "<td>$locked</td>";

        return $row;
    }

    /**
     * Returns either argument $msq_0 or $msq_1 depending on value of $bool
     *
     * @param $bool
     * @param $msg_0
     * @param $msg_1
     * @return mixed
     */
    protected function return_bool_msg($bool, $msg_0, $msg_1) {
        switch ($bool) {
            case 0:
                return $msg_0;
            case 1:
                return $msg_1;
            default:
                return $msg_0;
        }
    }

    /**
     * Formats date for display in the HTML user table.
     *
     * Called in $this->format_user_row()
     *
     * @param $date
     * @return false|string
     */
    protected function format_date($date) {
        $format = 'm/d/y H:ia';
        return date($format, strtotime($date));
    }

    /**
     * Public access for HTML user table.
     *
     * @return string
     */
    public function return_table() {
        return $this->table;
    }
}
<?php
require_once('check_admin.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Returns HTML for the admin navigation panel and "User Greeting" / Logout option.
 *
 * Left navigation panel includes links for index.php, users.php and reviews.php. The class checks the Rigby user's
 * privileges. If they do not have admin privileges, they will not be provided with a link to users.php.
 *
 * Used in:
 * - review_admin/detail_edit.php
 * - review_admin/index.php
 * - review_admin/reviews.php
 * - review_admin/users.php
 *
 * @param $file string Argument should be __FILE__ magic constant.
 */
class admin_bars {

    // Use trait check_admin to make sure user had admin creds.
    use check_admin;

    /**
     * @var bool True if user has admin privileges, Else false.
     */
    protected $is_admin;

    /**
     * @var string Accepts magic constant. Set by constructor.
     */
    protected $file;

    /**
     * Set username from $_SESSION['username'];
     *
     * @var string
     */
    protected $username_sess;

    /**
     * Holds greeting string set by $this->set_hello()
     * @var string
     */
    protected $hello_display;

    /**
     * Holds HTML toolbar which includes 'User Greeting' and logout option.
     * @var string
     */
    protected $toolbar;

    /**
     * Holds HTML sidebar. Set by $this->set_sidebar().
     * @var string
     */
    protected $sidebar;

    /**
     * admin_bars constructor.
     * @param $file string Argument is passed to $this->file.
     */
    public function __construct($file) {

        // Set username for greeting.
        $this->username_sess = isset($_SESSION['username']) ? $_SESSION['username'] : '';

        // Check if username has admin privileges.
        $this->is_admin = $this->chck_admin_priv($this->username_sess);

        // Get file to set the right navigation highlight for the nav panel.
        $this->file = $this->get_current_file($file);

        // Set Sidebar
        $this->sidebar = $this->set_sidebar($this->file, $this->is_admin);

        // Set Greeting message
        $this->hello_display = $this->set_hello();

        // Set Toolbar
        $this->toolbar = $this->set_toolbar();
    }

    /**
     * Set greeting message
     *
     * @return string
     */
    protected function set_hello() {
        $username = trim($this->username_sess);
        $hello = 'Hello';
        switch ($username) {
            case '':
                break;
            default:
                $hello .= " $username";
                break;
        }
        $hello .= '!';
        return $hello;
    }

    /**
     * Returns filename. This is used to set the navigation bar highlight. Current file name
     * is used later in
     *
     * @param $file string Should be __FILE__ magic constant passed from constructor
     * @return mixed
     */
    protected function get_current_file($file) {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    /**
     * Sets HTML for $this->toolbar.
     *
     * @return string
     */
    protected function set_toolbar() {
        $toolbar =  "<div id='edit_toolbar'>
                        <div id='login_user'>
                            <a href='#'><span>$this->hello_display</span></a>
                            <div id='user_opts'>
                              <div class='row'><a href='../review_admin/logout.php?logout=1'>Logout</a></div>
                            </div>
                        </div>
                    </div>";
        return $toolbar;
    }

    /**
     * Sets HTML for $this->sidebar.
     *
     * @param $file string Current filename.
     * @param $admin_priv bool True if username has admin privileges, Else false.
     * @return string HTML for the sidebar.
     */
    protected function set_sidebar($file, $admin_priv) {

        $sel_index   = '';
        $arrow_index = '';

        $sel_users   = '';
        $arrow_users = '';

        $sel_reviews   = '';
        $arrow_reviews = '';

        $sel_settings   = '';
        $arrow_settings = '';

        $sel_products   = '';
        $arrow_products = '';

        $selected = " class='selected'";
        $arrow    = "<div class='arrow-left'></div>";

        switch ($file) {
            case 'index':
                $sel_index      .= $selected;
                $arrow_index    .= $arrow;
                break;
            case 'users':
                $sel_users      .= $selected;
                $arrow_users    .= $arrow;
                break;
            case 'reviews':
                $sel_reviews    .= $selected;
                $arrow_reviews  .= $arrow;
                break;
            case 'detail_edit':
                $sel_reviews    .= $selected;
                $arrow_reviews  .= $arrow;
                break;
            case 'settings':
                $sel_settings   .= $selected;
                $arrow_settings .= $arrow;
                break;
            case 'products':
                $sel_products   .= $selected;
                $arrow_products .= $arrow;
            default:
                break;
        }

        // Start sidebar HTML
        $sidebar  = "<div id='edit_sidebar'><ul>";
        // Add Dashboard and Reviews options
        $sidebar .= "<li$sel_index><a href='index.php'><span>Dashboard</span></a>$arrow_index</li>"; // Add Dashboard
        $sidebar .= "<li$sel_reviews><a href='reviews.php'><span>Reviews</span></a>$arrow_reviews</li>";
        $sidebar .= "<li$sel_products><a href='../products.php'><span>Products</span></a>$arrow_products</li>";

        // If the Rigby user is admin, add Users and Settings options.
        if ($admin_priv == TRUE)
        {
            $sidebar .= "<li$sel_users><a href='users.php'><span>Users</span></a>$arrow_users</li>";
            $sidebar .= "<li$sel_settings><a href='settings.php'><span>Settings</span></a>$arrow_settings</li>";
        }

        $sidebar .= "</ul></div>"; // Close sidebar.
        return $sidebar;
    }

    /**
     * Returns HTML for toolbar (including 'User Greeting' and logout option.
     *
     * @return string
     */
    public function return_toolbar() {
        return $this->toolbar;
    }

    /**
     * Returns HTML for sidebar navigation.
     *
     * @return string
     */
    public function return_sidebar() {
        return $this->sidebar;
    }
}
<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Class logout
 *
 * Simple class that destroys the Rigby user's $_SESSION and then redirects to ../review_login.
 *
 * A 'logout' message is set.
 */
class logout {
    protected $login_page;

    public function __construct() {
        $this->login_page = "../review_login/";

    }
    public function destroy_sess() {
        session_start();
        session_destroy();
    }
    public function redirect() {
        session_start();
        $_SESSION['logout_msg'] = 1;
        $login_page = $this->login_page;
        header('Location: '.$login_page);        
    }
}

if (isset($_GET['logout'])) {
    $logout = new logout();
    
    $logout->destroy_sess();
    $logout->redirect();
}
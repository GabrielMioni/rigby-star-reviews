<?php
session_start();
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/display_message.php');

$build_display_msg = new display_message('pswd_reset_result');
$display_msg = $build_display_msg->return_msg_html();


$entered_name = '';

if (isset($_SESSION['entered_name'])) {
    $entered_name .= $_SESSION['entered_name'];
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Rigby Login</title>
        <link href="login.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald" rel="stylesheet">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    </head>
    <body>
        <div id='content'>
            <?php
            echo $display_msg;
            ?>
            <div id="main_login">
                <form id="rev_login" name="login" action="login_act.php" method="post">
                    <div class='form_row'>
                        <label for="user">
                            Username<br>
                        </label>
                        <input id='user' type="text" name="user_name" value="<?php echo $entered_name; ?>">
                    </div>
                    <div class='form_row'>
                        <label for='pswd'>
                            Password<br>
                        </label>
                        <input id='pswd' type="password" name="pswd">                        
                    </div>
                    <div class='form_row'>
                        <input id="set_remember" name="set_remember" value="remember" type="checkbox">
                        <label for='remember'>
                            Stay logged in
                        </label>    
                    </div>
                    <div class='form_row'>
                        <input type='submit' id='login_btn' name='login' value="Login">                        
                    </div>
                </form>
            </div>
            <div id="forgot_nav"><a href="forgot_password.php">Forgot your password?</a></div>
        </div>
    </body>
</html>

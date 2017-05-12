<?php

session_start();



$current_year = date('Y', time());

$entered_name = '';
$login_error  = '';

if (isset($_SESSION['entered_name'])) {
    $entered_name .= $_SESSION['entered_name'];
}
if (isset($_SESSION['login_error'])) {
    $login_error .= $_SESSION['login_error'];
}
if (isset($_SESSION['logout_msg'])) {
    $msg = $_SESSION['logout_msg'];
    $logout_msg = "<div class='logout'>You have been logged out.</div>";
} else {
    $logout_msg = '';
}

function set_login_error($login_error) {
    if ($login_error !== '') {
        $error_div  = "<div id='login_error'>";
        $error_div .= "<div class='bar'></div>";
        $error_div .= "<p>".$login_error."</p>";
        $error_div .= "</div>";
        
        echo $error_div;
    }
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
            <?php set_login_error($login_error); ?>
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
            <?php 
            echo $logout_msg;
            ?>
            
        </div>
    </body>
    <?php session_destroy(); ?>
</html>

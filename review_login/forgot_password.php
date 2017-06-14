<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/widgets/display_message.php');
require_once('forgot_password_display_form.php');

session_start();

$set_display_msg = new display_message('pswd_reset_result');
$display_message = $set_display_msg->return_msg_html();

if ($display_message == '' && !isset($_GET['reset_id']))
{
    $display_message = '<div class="good">Please enter the email address associated with your Rigby account. We\'ll send you a link that will let you reset your password.</div>';
}

if (isset($_SESSION['entered_email']))
{
    $entered_email = $_SESSION['entered_email'];
} else {
    $entered_email = '';
}

$set_form = new display_forgot_password_form();
$form = $set_form->return_html_form();

?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rigby Login</title>
    <link href="login.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Fjalla+One|Oswald" rel="stylesheet">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <style>
        #content {
            margin: 0 auto;
            position: relative;
            top: 150px;
        }
        .error,
        .good {
            font-family: "Oswald",sans-serif;
            box-sizing: border-box;
            margin: 0 auto 20px;
            padding: 0 0 0 10px;
            width: 300px;
        }
        .error {
            border-left: 5px solid red;
        }
        .good {
            border-left: 5px solid deepskyblue;
        }

        #main_login {
            font-family: "Oswald",sans-serif;
        }

        #main_login form input[type="text"],
        #main_login form input[type="password"] {
            height: 30px;
            min-width: 100%;
        }
        #main_login form input[type="submit"] {
            background-color: deepskyblue;
            border: 1px solid deepskyblue;
            box-shadow: none;
            color: white;
            font-size: 20px;
            height: 30px;
            width: 100%;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div id='content'>
    <?php
    echo $display_message;
    ?>
    <div id="main_login">
        <?php
        echo $form;
        ?>
    </div>
    <div id="forgot_nav"><a href="index.php">Go back to the login page.</a></div>
</div>
</body>
<?php unset($_SESSION['entered_email']); ?>
</html>

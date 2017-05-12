<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
session_start();

require_once('installer_class.php');

$start_installer = new installer();
$message = $start_installer->return_html();


?>

<html>
<title>Rigby Installer</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<link rel="stylesheet" href="installer_css.css">
<link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<head>

</head>
<body>
    <div id ='rigby_installer'>
        <?php
            echo $message;
        ?>
    </div>
</body>
</html>


<?php
/*
 *
        <div class="rigby_message good">
            <p>
                Rigby requires access to a MySQL server.
            </p>
            <form action="/projects/rigby/installer/index.php" method="post">
                <div class='inputs'>
                    <div class='form_row'>
                        <label for='sql_db'>Database: </label>
                        <input name='sql_db' type='text'>
                    </div>
                    <div class='form_row'>
                        <label for='sql_db'>Username: </label>
                        <input name='sql_un' type='text'>
                    </div>
                    <div class='form_row'>
                        <label for='sql_db'>Password: </label>
                        <input name='sql_pw' type='password'>
                    </div>
                </div>
                <button name="start_button" value="3" type="submit">Next</button>
            </form>
        </div>

    <div class="rigby_message">
        <table id="installer_check">
            <caption>Requirements</caption>
            <tr>
                <th>PHP version 5 or greater</th>
                <td class="true_td"><i class="fa fa-check" aria-hidden="true"></i></td>
            </tr>
            <tr>
                <th>PHP Sessions are enabled</th>
                <td class="false_td"><i class="fa fa-times" aria-hidden="true"></i></td>
            </tr>
        </table>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8"); ?>" method="post">
            <button name="start_button" value="1" type="submit">Start!</button>
        </form>
    </div>
 */
?>
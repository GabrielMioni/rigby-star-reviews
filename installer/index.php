<?php

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file provides a display for the Rigby installer. HTML is built and
 */

session_start();

require_once('installer_class.php');

$start_installer = new installer_class();
$installer_display = $start_installer->return_html();

?>

<html>
<head>
    <title>Rigby Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="installer_css.css">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div id ='rigby_installer'>
        <?php
            echo $installer_display;
        ?>
    </div>
</body>
</html>

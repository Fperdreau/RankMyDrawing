<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 28/09/2015
 * Time: 21:00
 */

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- JQuery -->
    <script type="text/javascript" src="../../js/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="../../js/jquery-ui.js"></script>
    <script type="text/javascript" src="timer.js"></script>

    <title>Test</title>
</head>

<body class="mainbody">

    Timer
    <div class="timer"></div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.timer').ExperimentTimer(30,1000);
        });
    </script>
</body>
</html>
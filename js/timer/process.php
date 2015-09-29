<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 28/09/2015
 * Time: 18:02
 */

if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}
session_start();

if (!empty($_POST['getTime'])) {
    $maxTime = $_POST['max'];
    if (empty($_SESSION['stopTime'])) {
        $_SESSION['stopTime'] = $maxTime;
    }

    echo json_encode($maxTime);
    exit;
}

<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 27/01/15
 * Time: 12:19
 */

@session_start();
// Includes required files (classes)
require_once($_SESSION['path_to_includes'].'includes.php');
require_once('functions.php');
date_default_timezone_set('Europe/Paris');

// Get site config
$config_file = $_SESSION['path_to_app']."admin/conf/config.php";
if (is_file($config_file)) {
    require_once($config_file);
}

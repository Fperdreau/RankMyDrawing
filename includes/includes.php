<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 03/11/14
 * Time: 11:23
 */

@session_start();
// Includes required files (classes)
require_once($_SESSION['path_to_includes'].'db_connect.php');
require_once($_SESSION['path_to_includes'].'users.php');
require_once($_SESSION['path_to_includes'].'myMail.php');
require_once($_SESSION['path_to_includes'].'site_config.php');
include_once($_SESSION['path_to_includes'].'functions.php');
include_once($_SESSION['path_to_includes'].'Elo.php');
include_once($_SESSION['path_to_includes'].'DrawRef.php');
include_once($_SESSION['path_to_includes'].'participant.php');
include_once($_SESSION['path_to_includes'].'Experiment.php');

date_default_timezone_set('Europe/Paris');

// Get site config
$config_file = $_SESSION['path_to_app']."admin/conf/config.php";
if (is_file($config_file)) {
    require_once($config_file);
}

<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 03/10/14
 * Time: 10:21
 */

@session_start();
chdir(dirname(__FILE__));
$_SESSION['path_to_app'] = '../';
$_SESSION['path_to_includes'] = $_SESSION['path_to_app']."includes/";
date_default_timezone_set('Europe/Paris');

// Includes
require_once($_SESSION['path_to_includes'].'includes.php');

// Run cron job
$backupfile = backup_db();
$filelink = json_encode($backupfile);
echo $filelink;

// Write log only if server request
if (empty($_GET['webproc'])) {
    $cronlog ='backup_log.txt';
    if (!is_file($cronlog)) {
        $fp = fopen($cronlog,"w");
        chmod($cronlog,0777);
    } else {
        $fp = fopen($cronlog,"a+");
        chmod($cronlog,0777);
    }
    $string = "[".date('Y-m-d H:i:s')."]: Backup successfully done.\r\n";
    fwrite($fp,$string);
    fclose($fp);
    chmod($cronlog,0644);
}

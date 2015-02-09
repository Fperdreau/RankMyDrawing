<?php
// Pastel Backup
// Marc Farré
// http://www.pastel.pro
// marc.farre@pastel.pro
// 2010-11-01
// Modified by Florian Perdreau: 15/10/2014
@session_start();
chdir(dirname(__FILE__));
$_SESSION['path_to_app'] = '../';
$_SESSION['path_to_includes'] = $_SESSION['path_to_app']."includes/";
date_default_timezone_set('Europe/Paris');

// Includes
require_once($_SESSION['path_to_includes'].'includes.php');

// db backup
$backupfile = backup_db(); // backup database
mail_backup($backupfile); // Send backup file to admins

// file backup
$zipfile = file_backup(); // Backup site files (archive)
$filelink = json_encode($zipfile);
echo $filelink;

// Write log only if server request
if (empty($_POST['webproc'])) {
    $cronlog ='fullbackup_log.txt';
    if (!is_file($cronlog)) {
        $fp = fopen($cronlog,"w");
        chmod($cronlog,0777);
    } else {
        $fp = fopen($cronlog,"a+");
        chmod($cronlog,0777);
    }
    $string = "[".date('Y-m-d H:i:s')."]: Full Backup successfully done.\r\n";
    fwrite($fp,$string);
    fclose($fp);
    chmod($cronlog,0644);
}
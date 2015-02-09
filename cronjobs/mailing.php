<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 05/11/2014
 * Time: 15:15
 */

/*if (empty($_GET['webproc'])) {
    $_SESSION['root_path'] = "/srv/www/vhosts/lpp.psycho.univ-paris5.fr";
} else {
    $_SESSION['root_path'] = $_SERVER['DOCUMENT_ROOT'];
}
$_SESSION['app_name'] = "/Pjc/";
$_SESSION['path_to_app'] = $_SESSION['root_path'].$_SESSION['app_name'];
$_SESSION['path_to_includes'] = $_SESSION['path_to_app']."includes/";
$_SESSION['path_to_html'] = $_SESSION['path_to_app']."php/";
$_SESSION['path_to_pages'] = $_SESSION['path_to_app']."pages/";

date_default_timezone_set('Europe/Paris');

// Includes
require_once($_SESSION['path_to_includes'].'includes.php');
require($_SESSION['path_to_app']."/admin/conf/config.php");*/

@session_start();
chdir(dirname(__FILE__));
$_SESSION['path_to_app'] = '../';
$_SESSION['path_to_includes'] = $_SESSION['path_to_app']."includes/";
date_default_timezone_set('Europe/Paris');

// Includes
require_once($_SESSION['path_to_includes'].'includes.php');

// Execute cron job
function mailing() {
    // Declare classes
    $mail = new myMail();
    $config = new site_config('get');
	
	// Count number of users
    $nusers = count($mail->get_mailinglist("notification"));
	
	// today's day
    $cur_date = strtolower(date("l"));

    if ($cur_date == $config->notification) {
        $content = $mail->advertise_mail();
        $body = $mail -> formatmail($content['body']);
        $subject = $content['subject'];
        if ($mail->send_to_mailinglist($subject,$body,"notification")) {
            $string = "[".date('Y-m-d H:i:s')."]: message sent successfully to $nusers users.\r\n";
        } else {
            $string = "[".date('Y-m-d H:i:s')."]: ERROR message not sent.\r\n";
        }
    
	    echo($string);
	
	    // Write log
	    $cronlog = 'mailing_log.txt';
	    if (!is_file($cronlog)) {
	        $fp = fopen($cronlog,"w");
	        chmod($cronlog,0777);
	    } else {
	        $fp = fopen($cronlog,"a+");
	        chmod($cronlog,0777);
	    }
	    fwrite($fp,$string);
	    fclose($fp);
	    chmod($cronlog,0644);
	} else {
		echo "<p>notification day: $config->notification</p>";
		echo "<p>Today: $cur_date</p>";
	}
	
}

// Run cron job
mailing();


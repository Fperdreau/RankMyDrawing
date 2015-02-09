<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 06/11/14
 * Time: 15:06
 */

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
    $pub = new presclass();
    $pub->get_nextpresentation();
	
	// Number of users
    $nusers = count($mail->get_mailinglist("reminder"));
	
	// Compare date of the next presentation to today
    $cur_date = strtolower(date("Y-m-d"));
    $reminder_day = date("Y-m-d",strtotime($pub->date." - $config->reminder days"));
		
    if ($cur_date == $reminder_day) {
        $content = $mail->reminder_Mail();
        $body = $mail -> formatmail($content['body']);
        $subject = $content['subject'];
        echo $body;
        if ($mail->send_to_mailinglist($subject,$body,"reminder")) {
            $string = "[".date('Y-m-d H:i:s')."]: message sent successfully to $nusers users.\r\n";
        } else {
            $string = "[".date('Y-m-d H:i:s')."]: ERROR message not sent.\r\n";
        }
		echo($string);

	    // Write log
	    $cronlog = 'reminder_log.txt';
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
        echo "nothing to send";
    }
}

// Run cron job
mailing();


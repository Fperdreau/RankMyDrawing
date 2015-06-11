<?php
/*
Copyright © 2014, Florian Perdreau
This file is part of Journal Club Manager.

Journal Club Manager is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Journal Club Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Define timezone
 *
 */
date_default_timezone_set('Europe/Paris');
if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}

/**
 * Define paths
 */
if(!defined('APP_NAME')) define('APP_NAME', basename(__DIR__));
if(!defined('PATH_TO_APP')) define('PATH_TO_APP', dirname(__FILE__));
if(!defined('PATH_TO_IMG')) define('PATH_TO_IMG', PATH_TO_APP.'/images/');
if(!defined('PATH_TO_INCLUDES')) define('PATH_TO_INCLUDES', PATH_TO_APP.'/includes/');
if(!defined('PATH_TO_PHP')) define('PATH_TO_PHP', PATH_TO_APP.'/php/');
if(!defined('PATH_TO_PAGES')) define('PATH_TO_PAGES', PATH_TO_APP.'/pages/');
if(!defined('PATH_TO_CONFIG')) define('PATH_TO_CONFIG', PATH_TO_APP.'/config/');
if(!defined('PATH_TO_LIBS')) define('PATH_TO_LIBS', PATH_TO_APP.'/libs/');

/**
 * Includes required files (classes)
 */
require_once(PATH_TO_INCLUDES.'AppDb.php');
require_once(PATH_TO_INCLUDES.'AppTable.php');
$includeList = scandir(PATH_TO_INCLUDES);
foreach ($includeList as $includeFile) {
    if (!in_array($includeFile,array('.','..','boot.php'))) {
        require_once(PATH_TO_INCLUDES.$includeFile);
    }
}

/**
 * Start session
 *
 */
SessionInstance::initsession();

/**
 * Declare classes
 *
 */
$db = new AppDb();
$AppConfig = new AppConfig($db,false);

/**
 * Browse release content and returns associative array with folders name as keys
 * @param $dir
 * @param array $foldertoexclude
 * @param array $filestoexclude
 * @return mixed
 */
function browsecontent($dir,$foldertoexclude=array(),$filestoexclude=array()) {
    $content[$dir] = array();
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            $filename = $dir."/".$file;
            if ($file != "." && $file != ".." && is_file($filename) && !in_array($filename,$filestoexclude)) {
                $content[$dir][] = $filename;
            } else if ($file != "." && $file != ".." && is_dir($dir.$file) && !in_array($dir.$file, $foldertoexclude) ) {
                $content[$dir] = browsecontent($dir.$file,$foldertoexclude,$filestoexclude);
            }
        }
        closedir($handle);
    }
    return $content;
}

/**
 * Check release integrity (presence of folders/files and file content)
 * @return bool
 */
function check_release_integrity() {
    $releasefolder = PATH_TO_APP.'/rmd/';
    $releasecontentfile = PATH_TO_APP.'/rmd/content.json';
    if (is_dir($releasefolder)) {
        require $releasecontentfile;
        $release_content = json_decode($content);
        $foldertoexclude = array('config','uploads','dev');
        $copied_release_content = browsecontent($releasefolder,$foldertoexclude);
        $diff = array_diff_assoc($release_content,$copied_release_content);
        $result['status'] = empty($diff) ? true:false;
        $result['msg'] = "";
    } else {
        $result['status'] = false;
        $result['msg'] = "<p id='warning'>The jcm folder containing the new release files should be placed at the root of your website</p>";
    }
    return json_encode($result);
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Process Installation
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

if (!empty($_POST['operation'])) {
    $operation = $_POST['operation'];

    // STEP 1: Check database credentials provided by the user
    if ($operation == "db_info") {
        $result = $db->testdb($_POST);
        echo json_encode($result);
        exit;
    }

    // STEP 2: Write database credentials to config.php file
    if ($operation == "do_conf") {
        $op = htmlspecialchars($_POST['op']);
        $op = $op == "new";

        $filename = PATH_TO_CONFIG . "config.php";
        $result = "";
        if (is_file($filename)) {
            unlink($filename);
        }

        // Make config folder
        $dirname = PATH_TO_CONFIG;
        if (is_dir($dirname) === false) {
            if (!mkdir($dirname, 0755)) {
                json_encode("Could not create config directory");
                exit;
            }
        }

        // Remove any pre-existent drawings files
        if ($op) {
            $dir = PATH_TO_IMG;
            $dh = opendir($dir);
            while (false !== ($filename = readdir($dh))) {
                if (is_dir($dir . '/' . $filename) && $filename !== '.' && $filename !== '..') {
                    deleteDirectory($dir . '/' . $filename);
                }
            }
        }

        // Write configuration information to config/config.php
        $fields_to_write = array("version", "host", "username", "passw", "dbname", "dbprefix");
        $config = array();
        foreach ($_POST as $name => $value) {
            if (in_array($name, $fields_to_write)) {
                $config[] = '"' . $name . '" => "' . $value . '"';
            }
        }
        $config = implode(',', $config);
        $string = '<?php $config = array(' . $config . '); ?>';

        // Create new config file
        $filename = PATH_TO_CONFIG . "config.php";
        if ($fp = fopen($filename, "w+")) {
            if (fwrite($fp, $string) == true) {
                fclose($fp);
            } else {
                $result = "Impossible to write";
                echo json_encode($result);
                exit;
            }
        } else {
            $result = "Impossible to open the file";
            echo json_encode($result);
            exit;
        }
        echo json_encode(true);
        exit;
    }

    // STEP 3: Do Backups before making any modifications to the db
    if ($operation == "backup") {
        $backup_file = backup_db();
        echo json_encode($backup_file);
        exit;
    }

    // STEP 4: Configure database
    if ($operation == "install_db") {

        $op = htmlspecialchars($_POST['op']);
        $op = $op == "new";
        $result = "";

        // Tables to create
        $tables_to_create = $db->tablesname;

        // First we remove any deprecated tables
        $old_tables = $db->getapptables();
        foreach ($old_tables as $old_table) {
            if (!in_array($old_table, $tables_to_create)) {
                if ($db->deletetable($old_table) == true) {
                    $result .= "<p id='success'>$old_table has been deleted because we do not longer need it</p>";
                } else {
                    $result .= "<p id='warning'>We could not remove $old_table although we do not longer need it</p>";
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // Create config table
        // Get default application settings
        $AppConfig = new AppConfig($db, false);
        $version = $AppConfig->version;
        if ($op) {
            $AppConfig->get();
        }

        $instructions = "<p>
                    During a succession of trials, pairs of hand-drawings are going to be presented, as well as
                    the original model (see opposite).<br><br>
                    Your task is to choose (by clicking on) which of the two drawings is closer to the original.
                    <b>Importantly</b>, do not make your decision on the basis of aesthetism or style!.<br>
                    </p>";
        $consent = "<p><strong>Experiment&apos;s aim :</strong> All data of this experiment are
                    collected for scientific reasons and will contribute to a better understanding
                    of the brain and of visual perception. These data might be published in
                    scientific journals.</p>
                    <p><strong>Experimental task: </strong>You are going to see pictures on your
                    monitor and you will have to give responses by clicking on a mouse.</p>
                    <p><strong>Remuneration: </strong>This experiment is not remunerated.</p>
                    <p><strong>Confidentiality: </strong>Your participation to this experiment is
                    confidential and your identity will not be recorder with your data.
                    We attribute a code to your responses, and the list relating your name to
                    this code will be destroyed once the data will be recorded and analyzed.
                    You have the right to access and to modify your data accordingly to the
                    Law on Information Technology andCivil Liberties. Any publication of the
                    results will not include identifying individual results.</p>
                    <p><strong>Participation: </strong>Your participation to this experiment is
                    entirely voluntary and you can, at any time, stop the experiment.</p>";

        $_POST['version'] = $version;
        $AppConfig->setup($op);
        if ($AppConfig->update($_POST) === true) {
            $result .= "<p id='success'> '" . $db->tablesname['AppConfig'] . "' updated</p>";
        } else {
            echo json_encode("<p id='warning'>'" . $db->tablesname['AppConfig'] . "' not updated</p>");
            exit;
        }
        $AppConfig->update(array('instruction'=>$instructions,'consent'=>$consent));

        // Create users table
        $Users = new Users($db);
        $Users->setup($op);

        // Create ref_drawings table
        $DrawRef = new DrawRef($db);
        $DrawRef->setup($op);

        // Create CronJobs table
        $CronJobs = new AppCron($db);
        $CronJobs->setup($op);

        echo json_encode($result);
        exit;
    }

    // Final step: create admin account (for new installation only)
    if ($operation == 'inst_admin') {
        $encrypted_pw = htmlspecialchars($_POST['password']);
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);

        $user = new Users($db);
        if ($user->make(array('username'=>$username, 'password'=>$encrypted_pw,'email'=>$email,'status'=>'admin'))) {
            $result = "<p id='success'>Admin account created</p>";
        } else {
            $result = "<p id='warning'>We could not create the admin account</p>";
        }
        echo json_encode($result);
        exit;
    }
}

/**
 * Get page content
 *
 */
if (!empty($_POST['getpagecontent'])) {
    $step = htmlspecialchars($_POST['getpagecontent']);
    $_SESSION['step'] = $step;
    $op = htmlspecialchars($_POST['op']);
    $new_version = $AppConfig->version;

    /**
     * Get configuration from previous installation
     * @var  $config
     *
     */
    $config = $db->get_config();
    $version = ($config['version'] !== false) ? $config['version']: false;

    if ($step == 1) {
        $title = "Welcome to the Journal Club Manager";
        if ($version == false) {
            $operation = "
                <p>Hello</p>
                <p>It seems that <i>RankMyDrawings</i> has never been installed here before.</p>
                <p>We are going to start from scratch... but do not worry, it is all automatic. We will guide you through the installation steps and you will only be required to provide us with some information regarding the hosting environment.</p>
                <p>Click on the 'next' button once you are ready to start.</p>
                <p>Thank you for your interest in <i>RankMyDrawings</i>
                <p style='text-align: center'><input type='button' id='submit' value='Start' class='start' data-op='new'></p>";
        } else {
            $operation = "
                <p>Hello</p>
                <p>The current version of <i>Journal Club Manager</i> installed here is $version. You are about to install the version $new_version.</p>
                <p>You can choose to either do an entirely new installation by clicking on 'New installation' or to simply update your current version to the new one by clicking on 'Update'.</p>
                <p id='warning'>Please, be aware that choosing to perform a new installation will completely erase all the data present in your <i>Journal Club Manager</i> database!!</p>
                <p style='text-align: center'>
                <input type='button' id='submit' value='New installation'  class='start' data-op='new'>
                <input type='button' id='submit' value='Update'  class='start' data-op='update'>
                </p>";
        }
    } elseif ($step == 2) {
        $config = $db->get_config();
        foreach ($config as $name=>$value) {
            $$name = $value;
        }
        $dbprefix = str_replace('_','',$config['dbprefix']);

        $title = "Step 1: Database configuration";
        $operation = "
			<form action='' method='post' name='install' id='db_info'>
                <input type='hidden' name='version' value='$AppConfig->version'>
                <input type='hidden' name='op' value='$op'/>
				<input type='hidden' name='db_info' value='true' />
                <div class='formcontrol' style='width: 30%'>
    				<label for='host'>Host Name</label>
    				<input name='host' type='text' value='$host'>
                </div>
                <div class='formcontrol' style='width: 30%'>
    				<label for='username'>Username</label>
    				<input name='username' type='text' value='$username'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='passw'>Password</label>
				    <input name='passw' type='password' value='$passw'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='dbname'>DB Name</label>
				    <input name='dbname' type='text' value='$dbname'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='dbprefix'>DB Prefix</label>
				    <input name='dbprefix' type='text' value='$dbprefix'>
                </div>
				<p style='text-align: right'><input type='submit' name='db_info' value='Next' id='submit' class='db_info' data-op='$op'></p>
			</form>
			<div class='feedback'></div>
		";
    } elseif ($step == 3) {
        $AppConfig->site_url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']).'/';
        $db->get_config();
        if ($op == "update") $AppConfig = new AppConfig($db);

        $title = "Step 2: Application configuration";
        $operation = "
            <form action='' method='post' name='install' id='install_db'>
                <input type='hidden' name='version' value='$AppConfig->version'>
                <input type='hidden' name='op' value='$op'/>
                <input type='hidden' name='install_db' value='true' />

                <div class='section_sub'>Journal Club Manager - Website</div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='sitetitle'>Site title</label>
                    <input name='sitetitle' type='text' value='$AppConfig->sitetitle'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='site_url'>Web path to root</label>
                    <input name='site_url' type='text' value='$AppConfig->site_url' size='30'>
                </div>

                <div class='section_sub'>Journal Club Manager - Mailing service</div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_from'>Sender Email address</label>
                    <input name='mail_from' type='text' value='$AppConfig->mail_from'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_from_name'>Sender name</label>
                    <input name='mail_from_name' type='text' value='$AppConfig->mail_from_name'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_host'>Email host</label>
                    <input name='mail_host' type='text' value='$AppConfig->mail_host'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='SMTP_secure'>SMTP access</label>
                    <select name='SMTP_secure'>
                        <option value='$AppConfig->SMTP_secure' selected='selected'>$AppConfig->SMTP_secure</option>
                        <option value='ssl'>ssl</option>
                        <option value='tls'>tls</option>
                        <option value='none'>none</option>
                     </select>
                 </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_port'>Email port</label>
                    <input name='mail_port' type='text' value='$AppConfig->mail_port'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_username'>Email username</label>
                    <input name='mail_username' type='text' value='$AppConfig->mail_username'>
                </div>
                <div class='formcontrol' style='width: 30%'>
                    <label for='mail_password'>Email password</label>
                    <input name='mail_password' type='password' value='$AppConfig->mail_password'>
                </div>

                <p style='text-align: right'><input type='submit' name='install_db' value='Next' id='submit' class='install_db' data-op='$op'></p>
            </form>
            <div class='feedback'></div>
        ";
    } elseif ($step == 4) {
        $title = "Step 3: Admin account creation";
        $operation = "
            <div class='feedback'></div>
			<form method='post' id='admin_creation'>
			    <div class='formcontrol' style='width: 30%'>
				    <label for='admin_username'>UserName : </label>
				    <input id='admin_username' type='text' name='username'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='admin_password'>Password : </label>
				    <input id='admin_password' type='password' name='password'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='admin_confpassword'>Confirm password: </label>
				    <input id='admin_confpassword' type='password' name='admin_confpassword'>
                </div>
                <div class='formcontrol' style='width: 30%'>
				    <label for='admin_email'>Email: </label>
				    <input type='text' name='email' id='admin_email'>
                </div>
				<input type='hidden' name='inst_admin' value='true'>
				<p style='text-align: right;'><input type='submit' name='submit' value='Next' id='submit' class='admin_creation' data-op='$op'></p>
			</form>
		";
    } elseif ($step == 5) {
        $title = "Installation complete!";
        $operation = "
		<p id='success'>Congratulations!</p>
		<p id='warning'> Now you can delete the 'install.php' file from the root folder of the application</p>
		<p style='text-align: right'><input type='submit' name='submit' value='Finish' id='submit' class='finish'></p>";
    }

    $result = "
	<div id='content'>
		<span id='pagename'>Installation</span>
		<div class='section_header' style='width: 300px'>$title</div>
		<div class='section_content'>
		    <div class='feedback'></div>
			<div id='operation'>$operation</div>
		</div>
	</div>";

    echo json_encode($result);
    exit;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <META NAME="description" CONTENT="Journal Club Manager. The easiest way to manage your lab's journal club.">
    <META NAME="keywords" CONTENT="Journal Club Manager">
    <link href='https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link type='text/css' rel='stylesheet' href="css/stylesheet.css"/>

    <!-- JQuery -->
    <script type="text/javascript" src="js/jquery-1.11.1.js"></script>

    <!-- Bunch of jQuery functions -->
    <script type="text/javascript">
        // Spin animation when a page is loading
        var $loading = $('#loading').hide();

        // Check email validity
        function checkemail(email) {
            var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
            return pattern.test(email);
        }

        //Show feedback
        var showfeedback = function(message,selector) {
            if (typeof selector == "undefined") {
                selector = ".feedback";
            }
            $(""+selector)
                .show()
                .html(message)
                .fadeOut(5000);
        };

        // Get page content
        var getpagecontent = function(step,op) {
            var stateObj = { page: 'install' };

            jQuery.ajax({
                url: 'install.php',
                type: 'POST',
                async: true,
                data: {
                    getpagecontent: step,
                    op: op},
                beforeSend: function() {
                    $('#loading').show();
                },
                complete: function() {
                    $('#loading').hide();
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    history.pushState(stateObj, 'install', 'install.php?step='+step+'&op='+op);

                    $('#loading').hide();
                    $('#pagecontent')
                        .fadeOut(100)
                        .html('<div>'+result+'</div>')
                        .fadeIn('slow');
                }
            });
        };

        var makeconfigfile = function(data) {
            data = modifyopeation(data,"do_conf");
            $('#operation').append("<p id='status'>Creation of configuration file</p>");
            // Make configuration file
            jQuery.ajax({
                url: 'install.php',
                type: 'POST',
                async: false,
                data: data,
                beforeSend: function() {
                    $('#loading').show();
                },
                complete: function() {
                    $('#loading').hide();
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    var html;
                    if (result === true) {
                        html = "<p id='success'>Configuration file created/updated</p>";
                    } else {
                        html = "<p id='warning'>"+result+"</p>";
                    }
                    $('#operation').append(html);
                }
            });
        };

        // Do a backup of the db before making any modification
        var dobackup = function() {
            $('#operation').append('<p id="status">Backup previous database</p>');
            // Make configuration file
            jQuery.ajax({
                url: 'install.php',
                type: 'POST',
                async: true,
                data: {operation: "backup"},
                beforeSend: function() {
                    $('#loading').show();
                },
                complete: function() {
                    $('#loading').hide();
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    var html = "<p id='success'>Backup created: "+result+"</p>";
                    $('#operation')
                        .empty()
                        .html(html)
                        .fadeIn(200);
                }
            });
            return false;
        };

        function modifyopeation(data,operation) {
            var index;
            // Find and replace `content` if there
            for (index = 0; index < data.length; ++index) {
                if (data[index].name == "operation") {
                    data[index].value = operation;
                    break;
                }
            }
            return data;
        }

        // Get url params ($_GET)
        var getParams = function() {
            var url = window.location.href;
            var splitted = url.split("?");
            if(splitted.length === 1) {
                return {};
            }
            var paramList = decodeURIComponent(splitted[1]).split("&");
            var params = {};
            for(var i = 0; i < paramList.length; i++) {
                var paramTuple = paramList[i].split("=");
                params[paramTuple[0]] = paramTuple[1];
            }
            return params;
        };

        $(document).ready(function () {
            $('.mainbody')
                .ready(function() {
                    // Get step
                    var params = getParams();
                    var step = (params.step == undefined) ? 1:params.step;
                    var op = (params.op == undefined) ? false:params.op;
                    getpagecontent(step, op);
                })

                /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
                 Installation/Update
                 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

                // Go to next installation step
                .on('click', '.start', function(e) {
                    e.preventDefault();
                    var op = $(this).attr('data-op');
                    getpagecontent(2,op);
                })

                // Go to next installation step
                .on('click', '.next', function(e) {
                    var op = $(this).attr('data-op');
                    e.preventDefault();
                    getpagecontent(2,op);
                })

                // Go to next installation step
                .on('click', '.finish', function(e) {
                    e.preventDefault();
                    window.location = "admin/index.php";
                })

                // Step 1->3: Launch database setup
                .on('click','.db_info',function(e) {
                    e.preventDefault();
                    var op = $(this).attr('data-op');
                    var formdata = $("#db_info").serializeArray();
                    formdata.push({name:"operation",value:"db_info"});

                    jQuery.ajax({
                        url: 'install.php',
                        type: 'POST',
                        async: true,
                        data: formdata,
                        success: function(data){
                            var result = jQuery.parseJSON(data);
                            if (result.status == false) {
                                showfeedback(result.msg);
                            } else {
                                showfeedback(result.msg);
                                $('#operation').empty();

                                // Make config.php file
                                makeconfigfile(formdata);

                                // Go to the next step
                                setTimeout(function(){
                                    getpagecontent(3,op);
                                },2000);
                            }
                        }
                    });
                })

                // Launch database setup
                .on('click','.install_db',function(e) {
                    e.preventDefault();
                    var op = $(this).attr('data-op');
                    var formdata = $("#install_db").serializeArray();
                    formdata.push({name:"operation",value:"install_db"});
                    $('#operation').empty();

                    // First we backup the db before making any modifications
                    dobackup();

                    // Next, configure database
                    jQuery.ajax({
                        url: 'install.php',
                        type: 'POST',
                        async: true,
                        data: formdata,
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        complete: function() {
                            $('#loading').hide();
                        },
                        success: function(data){
                            var result = jQuery.parseJSON(data);
                            $('#operation').append(result);

                            // Go to next step
                            setTimeout(function() {
                                if (op !== "update") {
                                    getpagecontent(4,op);
                                } else {
                                    getpagecontent(5,op);
                                }
                            },2000);
                        }
                    });
                })

                // Final step: Create admin account
                .on('click','.admin_creation',function(e) {
                    e.preventDefault();
                    var op = $(this).attr('data-op');
                    var username = $("input#admin_username").val();
                    var password = $("input#admin_password").val();
                    var conf_password = $("input#admin_confpassword").val();
                    var email = $("input#admin_email").val();

                    if (username == "") {
                        showfeedback('<p id="warning">This field is required</p>','.feedback');
                        $("input#admin_username").focus();
                        return false;
                    }

                    if (password == "") {
                        showfeedback('<p id="warning">This field is required</p>','.feedback');
                        $("input#admin_password").focus();
                        return false;
                    }

                    if (conf_password == "") {
                        showfeedback('<p id="warning">This field is required</p>','.feedback');
                        $("input#admin_confpassword").focus();
                        return false;
                    }

                    if (conf_password != password) {
                        showfeedback('<p id="warning">Password must match</p>');
                        $("input#admin_confpassword").focus();
                        return false;
                    }

                    if (email == "") {
                        showfeedback('<p id="warning">This field is required</p>','.feedback');
                        $("input#admin_email").focus();
                        return false;
                    }

                    if (!checkemail(email)) {
                        showfeedback('<p id="warning">Oops, this is an invalid email</p>','.feedback');
                        $("input#admin_email").focus();
                        return false;
                    }

                    jQuery.ajax({
                        url: 'install.php',
                        type: 'POST',
                        async: true,
                        data: {
                            operation: "inst_admin",
                            op: op,
                            username: username,
                            password: password,
                            email: email,
                            conf_password: conf_password},
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        complete: function() {
                            $('#loading').hide();
                        },
                        success: function(data){
                            var result = jQuery.parseJSON(data);
                            showfeedback(result);
                            getpagecontent(5,op);
                        }
                    });
                });
        });
    </script>
    <title>RankMyDrawings - Installation</title>
</head>

<body class="mainbody">
<!-- Header section -->
<div id="mainheader">
    <!-- Header section -->
    <div class="header">
        <div class='header_container'>
            <div id='title'>
                <span id='sitetitle'>RankMyDrawings</span>
            </div>
        </div>
    </div>
</div>

<!-- Core section -->
<div class="core">
    <div id="loading"></div>
    <div id="pagecontent"></div>
</div>

<!-- Footer section -->
<div id="footer">
            <span id="sign"><?php echo "<a href='$AppConfig->repository' target='_blank'>$AppConfig->app_name $AppConfig->version</a>
             | <a href='http://www.gnu.org/licenses/agpl-3.0.html' target='_blank'>GNU AGPL v3 </a>
             | <a href='http://www.florianperdreau.fr' target='_blank'>&copy2014 $AppConfig->author</a>" ?></span>
</div>
</body>
</html>

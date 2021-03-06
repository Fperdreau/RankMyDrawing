<?php
/**
 * page for installation
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of Journal Club Manager.
 *
 * RankMyDrawings is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * RankMyDrawings is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * BOOTING PART
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
include_once(PATH_TO_INCLUDES.'AppDb.php');
include_once(PATH_TO_INCLUDES.'AppTable.php');
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
    $releasefolder = PATH_TO_APP.'/jcm/';
    $releasecontentfile = PATH_TO_APP.'/jcm/content.json';
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

/**
 * Patching database tables for version older than 1.2.
 */
function patching() {

    $version = $_SESSION['installed_version'];

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

        $filename = PATH_TO_CONFIG . "config.php";
        $result = "";
        if (is_file($filename)) {
            unlink($filename);
        }

        // Make config folder
        $dirname = PATH_TO_CONFIG;
        if (is_dir($dirname) === false) {
            if (!mkdir($dirname, 0755)) {
                $result['status'] = false;
                $result['msg'] = "Could not create config directory";
                echo json_encode($result);
                exit;
            }
        }

        // Make uploads folder
        $dirname = PATH_TO_APP . "/uploads/";
        if (is_dir($dirname) === false) {
            if (!mkdir($dirname, 0755)) {
                $result['status'] = false;
                $result['msg'] = "Could not create uploads directory";
                echo json_encode($result);
                exit;
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
        if ($fp = fopen($filename, "w+")) {
            if (fwrite($fp, $string) == true) {
                fclose($fp);
                $result['status'] = true;
                $result['msg'] = "Configuration file created!";
            } else {
                $result['status'] = false;
                $result['msg'] = "Impossible to write";
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "Impossible to open the file";
        }
        echo json_encode($result);
        exit;
    }

    // STEP 3: Do Backups before making any modifications to the db
    if ($operation == "backup") {
        include('cronjobs/DbBackup.php');
        $backup = new DbBackup($db);
        $backup->run();
        $result['msg'] = "Backup is complete!";
        $result['status'] = true;
        echo json_encode($result);
        exit;
    }

    // STEP 4: Configure database
    if ($operation == "install_db") {

        $op = htmlspecialchars($_POST['op']);
        $op = $op == "new";

        // Tables to create
        $tables_to_create = $db->tablesname;

        // Get default application settings
        $AppConfig = new AppConfig($db, false);
        $version = $AppConfig->version; // New version number
        if ($op === true) {
            $AppConfig->get();
        }
        $_POST['version'] = $version;

        // Create config table
        $AppConfig->setup($op);
        $AppConfig->get();
        $_POST['instruction'] = "<p>
                    During a succession of trials, pairs of hand-drawings are going to be presented, as well as
                    the original model (see opposite).<br><br>
                    Your task is to choose (by clicking on) which of the two drawings is closer to the original.
                    <b>Importantly</b>, do not make your decision on the basis of aesthetism or style!.<br>
                    </p>";
        $_POST['consent'] = "<p><strong>Experiment&apos;s aim :</strong> All data of this experiment are
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
        $AppConfig->update($_POST);

        // Create users table
        $Users = new Users($db);
        $Users->setup($op);

        // Create ref_drawings table
        $DrawRef = new DrawRef($db);
        $DrawRef->setup($op);

        // Create ref_drawings table
        $Uploads = new Uploads($db);
        $Uploads->setup($op);

        // Create CronJobs table
        $CronJobs = new AppCron($db);
        $CronJobs->setup($op);

        // Apply patch if required
        if ($op == false) {
            patching();
        }

        $result['msg'] = "Database installation complete!";
        $result['status'] = true;
        echo json_encode($result);
        exit;
    }

    // Final step: create admin account (for new installation only)
    if ($operation == 'admin_creation') {
        $user = new Users($db);
        $result = $user->make($_POST);
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
    $_SESSION['installed_version'] = $version;

    if ($step == 1) {
        $title = "Welcome to RankMyDrawings";
        if ($version == false) {
            $operation = "
                <p>Hello</p>
                <p>It seems that <i>RankMyDrawings</i> has never been installed here before.</p>
                <p>We are going to start from scratch... but do not worry, it is all automatic. We will guide you through the installation steps and you will only be required to provide us with some information regarding the hosting environment.</p>
                <p>Click on the 'next' button once you are ready to start.</p>
                <p>Thank you for your interest in <i>RankMyDrawings</i>
                <p style='text-align: center'><input type='button' value='Start' class='start' data-op='new'></p>";
        } else {
            $operation = "
                <p>Hello</p>
                <p>The current version of <i>RankMyDrawings</i> installed here is $version. You are about to install the version $new_version.</p>
                <p>You can choose to either do an entirely new installation by clicking on 'New installation' or to simply update your current version to the new one by clicking on 'Update'.</p>
                <p id='warning'>Please, be aware that choosing to perform a new installation will completely erase all the data present in your <i>RankMyDrawings</i> database!!</p>
                <p style='text-align: center'>
                <input type='button' value='New installation'  class='start' data-op='new'>
                <input type='button' value='Update' class='start' data-op='update'>
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
                <input type='hidden' name='operation' value='db_info'/>
				<input type='hidden' name='db_info' value='true' />
                <div class='formcontrol'>
    				<label for='host'>Host Name</label>
    				<input name='host' type='text' value='$host' required autocomplete='on'>
                </div>
                <div class='formcontrol'>
    				<label for='username'>Username</label>
    				<input name='username' type='text' value='$username' required autocomplete='on'>
                </div>
                <div class='formcontrol'>
				    <label for='passw'>Password</label>
				    <input name='passw' type='password' value='$passw'>
                </div>
                <div class='formcontrol'>
				    <label for='dbname'>DB Name</label>
				    <input name='dbname' type='text' value='$dbname' required autocomplete='on'>
                </div>
                <div class='formcontrol'>
				    <label for='dbprefix'>DB Prefix</label>
				    <input name='dbprefix' type='text' value='$dbprefix' required autocomplete='on'>
                </div>
                <div class='submit_btns'>
                    <input type='submit' value='Next' class='proceed'>
                </div>
			</form>
			<div class='feedback'></div>
		";
    } elseif ($step == 3) {
        $db->get_config();
        if ($op == "update") $AppConfig = new AppConfig($db);
        $AppConfig->site_url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']).'/';

        $title = "Step 2: Application configuration";
        $operation = "
            <form action='' method='post' name='install' id='install_db'>
                <input type='hidden' name='version' value='$AppConfig->version'>
                <input type='hidden' name='op' value='$op'/>
                <input type='hidden' name='operation' value='install_db'/>
                <input type='hidden' name='site_url' value='$AppConfig->site_url'/>

                <h3>Mailing service</h3>
                <div class='formcontrol'>
                    <label for='mail_from'>Sender Email address</label>
                    <input name='mail_from' type='email' value='$AppConfig->mail_from'>
                </div>
                <div class='formcontrol'>
                    <label for='mail_from_name'>Sender name</label>
                    <input name='mail_from_name' type='text' value='$AppConfig->mail_from_name'>
                </div>
                <div class='formcontrol'>
                    <label for='mail_host'>Email host</label>
                    <input name='mail_host' type='text' value='$AppConfig->mail_host'>
                </div>
                <div class='formcontrol'>
                    <label for='SMTP_secure'>SMTP access</label>
                    <select name='SMTP_secure'>
                        <option value='$AppConfig->SMTP_secure' selected='selected'>$AppConfig->SMTP_secure</option>
                        <option value='ssl'>ssl</option>
                        <option value='tls'>tls</option>
                        <option value='none'>none</option>
                     </select>
                 </div>
                <div class='formcontrol'>
                    <label for='mail_port'>Email port</label>
                    <input name='mail_port' type='text' value='$AppConfig->mail_port'>
                </div>
                <div class='formcontrol'>
                    <label for='mail_username'>Email username</label>
                    <input name='mail_username' type='text' value='$AppConfig->mail_username'>
                </div>
                <div class='formcontrol'>
                    <label for='mail_password'>Email password</label>
                    <input name='mail_password' type='password' value='$AppConfig->mail_password'>
                </div>

                <div class='submit_btns'>
                    <input type='submit' value='Next' class='proceed'>
                </div>
            </form>
            <div class='feedback'></div>
        ";
    } elseif ($step == 4) {
        $title = "Step 3: Admin account creation";
        $operation = "
            <div class='feedback'></div>
			<form id='admin_creation'>
                <input type='hidden' name='op' value='$op'/>
                <input type='hidden' name='operation' value='admin_creation'/>
                <input type='hidden' name='status' value='admin'/>
                <input type='hidden' name='active' value='1'/>

			    <div class='formcontrol'>
				    <label for='username'>UserName</label>
				    <input type='text' name='username' required autocomplete='on'>
                </div>
                <div class='formcontrol'>
				    <label for='password'>Password</label>
				    <input type='password' name='password' required>
                </div>
                <div class='formcontrol'>
				    <label for='conf_password'>Confirm password</label>
				    <input type='password' name='conf_password' required>
                </div>
                <div class='formcontrol'>
				    <label for='admin_email'>Email</label>
				    <input type='email' name='email' required autocomplete='on'>
                </div>
                <input type='hidden' name='status' value='admin'>
                <div class='submit_btns'>
                    <input type='submit' value='Next' class='admin_creation'>
                </div>
			</form>
		";
    } elseif ($step == 5) {
        $title = "Installation complete!";
        $operation = "
		<p id='success'>Congratulations!</p>
		<p id='warning'>Now you can delete the 'install.php' file from the root folder of the application</p>
		<p style='text-align: right'><input type='button' value='Finish' class='finish'></p>";
    }

    $result['content'] = "
		<h2>$title</h2>
		<section>
		    <div class='feedback'></div>
			<div id='operation'>$operation</div>
		</section>
	";
    $result['step'] = $step;
    $result['op'] = $op;
    echo json_encode($result);
    exit;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <META NAME="description" CONTENT="Journal Club Manager. The easiest way to manage your lab's journal club.">
    <META NAME="keywords" CONTENT="Journal Club Manager">
    <link href='https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link type='text/css' rel='stylesheet' href="css/stylesheet.css"/>

    <style type="text/css">
        .box {
            background: #FFFFFF;
            width: 60%;
            padding: 20px;
            margin: 2% auto;
            border: 1px solid #eeeeee;
        }
    </style>

    <!-- JQuery -->
    <script type="text/javascript" src="js/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="js/form.js"></script>

    <!-- Bunch of jQuery functions -->
    <script type="text/javascript">

        // Get url params ($_GET)
        function getParams() {
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
        }

        // Get page content
        function getpagecontent(step,op) {
            var stateObj = { page: 'install' };
            var div = $('#pagecontent');

            var callback = function(result) {
                history.pushState(stateObj, 'install', "install.php?step=" + result.step + "&op=" + result.op);
                $('#pagecontent').html(result.content).fadeIn(200);
            };
            var data = {getpagecontent: step, op: op};
            processAjax(div,data,callback,'install.php');
        }

        /**
         * Show loading animation
         */
        function loadingDiv(el) {
            el
                .css('position','relative')
                .append("<div class='loadingDiv' style='width: 100%; height: 100%;'></div>")
                .show();
        }

        /**
         * Remove loading animation at the end of an AJAX request
         * @param el: DOM element in which we show the animation
         */
        function removeLoading(el) {
            el.fadeIn(200);
            el.find('.loadingDiv')
                .fadeOut(1000)
                .remove();
        }

        /**
         * Create configuration file
         */
        function makeConfigFile(data) {
            data = modOperation(data,'do_conf');
            var operationDiv = $('#operation');
            processAjax(operationDiv,data,false,'install.php');
        }

        /**
         *  Do a backup of the db before making any modification
         */
        function doBackup() {
            var data = {operation: "backup"};
            var operationDiv = $('#operation');
            processAjax(operationDiv,data,false,'install.php');
        }

        /**
         *  Check consistency between session/presentation tables
         */
        function checkDb() {
            var data = {operation: "checkDb"};
            var operationDiv = $('#operation');
            processAjax(operationDiv,data,false,'install.php');
        }

        function modOperation(data,operation) {
            var i;
            // Find and replace `content` if there
            for (i = 0; i < data.length; ++i) {
                if (data[i].name == "operation") {
                    data[i].value = operation;
                    break;
                }
            }
            return data;
        }

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
                .on('click', '.finish', function(e) {
                    e.preventDefault();
                    window.location = "admin/index.php";
                })

                // Step 1->3: Launch database setup
                .on('click','.proceed',function(e) {
                    e.preventDefault();
                    var input = $(this);
                    var form = input.length > 0 ? $(input[0].form) : $();
                    var op = form.find('input[name="op"]').val();
                    var operation = form.find('input[name="operation"]').val();
                    var data = form.serializeArray();
                    var callback = false;
                    var operationDiv = $('#operation');

                    if (!checkform(form)) return false;

                    if (operation === 'db_info') {
                        callback = function() {

                            // Create configuration file
                            makeConfigFile(data);

                            // Go to the next step
                            getpagecontent(3,op);
                        };
                    } else if (operation === 'install_db') {
                        // First we backup the db before making any modifications
                        doBackup();

                        callback = function() {
                            // Check database consistency
                            checkDb();
                            // Go to next step
                            if (op !== "update") {
                                getpagecontent(4,op);
                            } else {
                                getpagecontent(5,op);
                            }
                        };
                    }
                    processAjax(operationDiv,data,callback,'install.php');
                })

                // Final step: Create admin account
                .on('click','.admin_creation',function(e) {
                    e.preventDefault();
                    var form = $(this).length > 0 ? $($(this)[0].form) : $();
                    var op = form.find('input[name="op"]').val();
                    if (!checkform(form)) {return false;}
                    var callback = function(result) {
                        if (result.status == true) {
                            getpagecontent(5,op);
                        }
                    };
                    processForm(form,callback,'install.php');
                });
        });
    </script>
    <style type="text/css">
        #footer {
            position: relative;
            z-index: 0;
            width: 100%;
            min-height: 100px;
            text-align: center;
            vertical-align: middle;
            margin: 20px auto;
            background-color: rgba(50,50,50,1);
            padding: 0;
        }
    </style>
    <title>RankMyDrawings - Installation</title>
</head>

<body class="mainbody" style="background: #FdFdFd;">

<div id="bodytable">
    <!-- Header section -->
    <div class="box" style='text-align: center; font-size: 1.7em; color: rgba(68,68,68,1); font-weight: 300;'>
        RankMyDrawings - Installation
    </div>

    <!-- Core section -->
    <div class="box" style="min-height: 400px;">
        <div id="pagecontent" style="padding: 20px 0;"></div>
    </div>

    <footer id="footer" style='width: 60%; padding: 20px; margin: 2% auto;'>
        <div id="appTitle"><?php echo $AppConfig->app_name; ?></div>
        <div id="appVersion">Version <?php echo $AppConfig->version; ?></div>
        <div id="sign">
            <div><?php echo "<a href='$AppConfig->repository' target='_blank'>Sources</a></div>
                <div><a href='http://www.gnu.org/licenses/agpl-3.0.html' target='_blank'>GNU AGPL v3</a></div>
                <div><a href='http://www.florianperdreau.fr' target='_blank'>&copy2014 $AppConfig->author</a>" ?></div>
        </div>
    </footer>
</div>

</body>

</html>

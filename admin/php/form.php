<?php
/*
Copyright © 2014, F. Perdreau, Radboud University Nijmegen
=======
This file is part of RankMyDrawings.

RankMyDrawings is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

RankMyDrawings is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.

*/

// Includes required files (classes)
require_once('../includes/includes.php');

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Login/Logout
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Logout
if (!empty($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo json_encode(true);
    exit;
}

// Check login
if (!empty($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $user = new Users($db,$username);
    $result = $user->login($_POST);
    echo json_encode($result);
    exit;
}

if (!empty($_POST['isLogged'])) {
    $result = (isset($_SESSION['logok']) && $_SESSION['logok']);
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Admin information
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

// Send password change request if email exists in database
if (!empty($_POST['change_pw'])) {
    $email = htmlspecialchars($_POST['email']);
    $user = new Users($db);

    if ($user->mail_exist($email)) {
        $username = $db ->getinfo($db->tablesname['User'],'username',array("email"),array("'$email'"));
        $user->get($username);
        $reset_url = $AppConfig->site_url."index.php?page=renew&hash=$user->hash&email=$user->email";
        $subject = "Change password";
        $content = "
            Hello $user->username,<br>
            <p>You requested us to change your password.</p>
            <p>To reset your password, click on this link:
            <br><a href='$reset_url'>$reset_url</a></p>
            <br>
            <p>If you did not request this change, please ignore this email.</p>
            ";

        $body = $AppMail->formatmail($content);
        if ($AppMail->send_mail($email,$subject,$body)) {
            $result['msg'] = "An email has been sent to your address with further information";
            $result['status'] = true;
        } else {
            $result['msg'] = "Oops, we couldn't send you the verification email";
            $result['status'] = false;
        }
    } else {
        $result['msg'] = "This email does not exist in our database";
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

// Change user password after confirmation
if (!empty($_POST['conf_changepw'])) {
    $username = htmlspecialchars($_POST['username']);
    $oldpassword = htmlspecialchars($_POST['old_password']);
    $password = htmlspecialchars($_POST['password']);

	$user = new Users($db,$username);
	if ($user->check_pwd($oldpassword)) {
	    $crypt_pwd = $user->crypt_pwd($password);
	    $db->updatecontent($db->tablesname['Users'],array("password"=>$crypt_pwd),array("username"=>$username));
        $result['status'] = true;
	} else {
        $result['status'] = false;
        $result['msg'] = "Wrong password!";
    }

    echo json_encode($result);
    exit;
}

// Process user modifications
if (!empty($_POST['mod_admininfo'])) {
    $user = new Users($db,$_POST['username']);
    if ($user -> update($_POST)) {
        $result['status'] = true;
        $result['msg'] = "The modification has been made!";
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Reference Drawing settings
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Modify ref drawing settings
if (!empty($_POST['mod_ref_params'])) {
    $ref = new DrawRef($db,$_POST['refid']);
    if ($ref->update($_POST)) {
        $result['msg'] = "Modifications have been successfully made";
        $result['status'] = true;
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

// Display ref drawing content (instruction/consent form)
if (!empty($_POST['display_content'])) {
    $ref_id = $_POST['refid'];
    $lang = $_POST['lang'];
    $ref = new DrawRef($db,$ref_id);
    $inst = $ref->get_content("instruction");
    $cons = $ref->get_content("consent");
    $result = array(
        'instruction' => $inst["$lang"],
        'consent' => $cons["$lang"]);
    echo json_encode($result);
    exit;
}

// Load text area with content to modify
if (!empty($_POST['cha_content'])) {
    $lang = htmlspecialchars($_POST['lang']);
    $ref_id = htmlspecialchars($_POST['refid']);
    $type = htmlspecialchars($_POST['type']);
    $ref = new DrawRef($db,$ref_id);
    $content = $ref->get_content($type);
    $result = $content["$lang"];
    echo json_encode($result);
    exit;
}

// Add instruction
if (!empty($_POST['add_content'])) {
    $lang = htmlspecialchars($_POST['lang']);
    $ref_id = htmlspecialchars($_POST['refid']);
    $instruction = $db->escape_query($_POST['instruction']);
    $consent = $db->escape_query($_POST['consent']);
    $table = $db->dbprefix.'_'.$ref_id."_content";
    $result = $db -> addcontent($table,array('type'=>'instruction','lang'=>$lang,'content'=>$instruction));
    $result = $db -> addcontent($table,array('type'=>'consent','lang'=>$lang,'content'=>$consent));
    echo json_encode($result);
    exit;
}

// Modify instruction
if (!empty($_POST['mod_content'])) {
    $lang = $_POST['lang'];
    $ref_id = $_POST['refid'];
    $type = $_POST['type'];
    $content = $db->escape_query($_POST['content']);

    $ref = new DrawRef($db,$ref_id);
    $table = $db->dbprefix.'_'.$ref->file_id.'_content';
    $result = $db -> updatecontent($table,array('content'=>$content),array('type'=>$type,'lang'=>$lang));
    echo json_encode($result);
    exit;
}

// Delete instruction
if (!empty($_POST['del_content'])) {
    $ref_id = $_POST['refid'];
    $lang = $_POST['lang'];
    $type = $_POST['type'];
    $table = $db->dbprefix.'_'.$ref->file_id.'_content';
    $result = $db -> deletecontent($content_table,array('type','lang','file_id'),array("'$type'","'$lang'","'$ref_id'"));
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Drawing Management
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Get item information
if (!empty($_POST['getItem'])) {
    $name = $_POST['itemid'];
    $refid = $_POST['refid'];
    $item = new Ranking($db,$refid,$name);
    $del_url = PATH_TO_IMG."delete.png";
    $thumb_url = PATH_TO_IMG."$item->refid/thumb/thumb_$item->filename";
    $result = "
        <div class='item' id='item_$item->file_id'>
            <div style='font-size: 12px; float: left;'>Score: $item->score</div>
            <div class='delete_btn_item' id='$item->file_id' data-item='$item->refid'>
                <img src='$del_url' alt='Delete $item->file_id' style='width: 10px; height: 10px;'>
            </div>
            <div class='thumb'>
                <a rel='item_leanModal' id='modal_trigger_showitem' href='#item_modal' class='modal_trigger' data-ref='$item->refid' data-item='$item->file_id'><img src='$thumb_url' class='thumb' alt='$item->file_id'></a>
            </div>
        </div>
    ";
    echo json_encode($result);
    exit;
}

// Get items list
if (!empty($_POST['getItems'])) {
    $refid = $_POST['refid'];
    $ref = new DrawRef($db,$refid);
    $result = $ref->displayitems();
    echo json_encode($result);
    exit;
}

// Check availability of the new reference drawing's label
if (!empty($_POST['check_availability'])) {
    include_once('../js/myuploads/uploader.php');
    $refid = $_POST['refid'];
    $ref = new DrawRef($db);
    if ($ref->exists($refid)) {
        $result['status'] = true;
        $result['msg'] = "This name is already taken. Please choose another one.";
    } else {
        $result['status'] = false;
        $result['msg'] = uploader(array(),$refid);
    }
    echo json_encode($result);
    exit;
}

// Sort items
if (!empty($_POST['sortitems'])) {
    $filter = htmlspecialchars($_POST['sortitems']);
    $refdraw = htmlspecialchars($_POST['refdraw']);
    $ref = new DrawRef($db,$refdraw);
    if ($filter == "") {
        $filter = null;
    }
    $result = $ref->displayitems($filter);
    echo json_encode($result);
    exit;
}

// Delete reference drawing
if (!empty($_POST['deleteref'])) {
    $file_id = htmlspecialchars($_POST['deleteref']);
    $ref = new DrawRef($db,$file_id);
    $result = $ref->delete();
    echo json_encode($result);
    exit;
}

// Show item description
if(!empty($_POST['show_item'])) {
    $refid = $_POST['refid'];
    $itemid = $_POST['itemid'];
    $item = new Ranking($db,$refid,$itemid);

    // Add a delete link (only for admin and organizers or the authors)
    $result['content'] = $item->showDetails();
    $result['item'] = $itemid;
    echo json_encode($result);
    exit;
}

// Show item description
if(!empty($_POST['show_item_settings'])) {
    $refid = $_POST['refid'];
    $ref = new DrawRef($db, $refid);

    // Add a delete link (only for admin and organizers or the authors)
    $result['content'] = $ref->showSettings();
    $result['ref'] = $refid;
    echo json_encode($result);
    exit;
}

// Delete item drawing
if (!empty($_POST['delete_item'])) {
    $item_id = htmlspecialchars($_POST['delete_item']);
    $drawref = htmlspecialchars($_POST['drawref']);

    $ref = new Ranking($db,$drawref,$item_id);
    $result = $ref->delete();
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Common to Plugins/Scheduled tasks
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Install/uninstall cron jobs
if (!empty($_POST['installDep'])) {
    $name = $_POST['installDep'];
    $op = $_POST['op'];
    $type = $_POST['type'];
    $App = ($type == 'plugin') ? new AppPlugins($db):new AppCron($db);
    $thisApp = $App->instantiate($name);
    if ($op == 'install') {
        if ($thisApp->install()) {
            $result['status'] = true;
            $result['msg'] = "$name has been installed!";
        } else {
            $result['status'] = false;
        }
    } elseif ($op == 'uninstall') {
        if ($thisApp->delete()) {
            $result['status'] = true;
            $result['msg'] = "$name has been deleted!";
        } else {
            $result['status'] = false;
        }
    } else {
        $result['msg'] = $thisApp->run();
        $result['status'] = true;
    }
    echo json_encode($result);
    exit;
}

// Get settings
if (!empty($_POST['getOpt'])) {
    $name = htmlspecialchars($_POST['getOpt']);
    $op = htmlspecialchars($_POST['op']);
    $App = ($op == 'plugin') ? new AppPlugins($db):new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    $result = $thisApp->displayOpt();
    echo json_encode($result);
    exit;
}

// Modify settings
if (!empty($_POST['modOpt'])) {
    $name = htmlspecialchars($_POST['modOpt']);
    $op = htmlspecialchars($_POST['op']);
    $data = $_POST['data'];
    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    if ($thisApp->update(array('options'=>$data))) {
        $result['status'] = true;
        $result['msg'] = "$name's settings successfully updated!";
    } else {
        $result['status'] = true;
    }
    echo json_encode($result);
    exit;
}

// Modify status
if (!empty($_POST['modStatus'])) {
    $name = htmlspecialchars($_POST['modStatus']);
    $status = htmlspecialchars($_POST['status']);
    $op = htmlspecialchars($_POST['op']);
    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    $thisApp->status = $status;
    if ($thisApp->isInstalled()) {
        $result = $thisApp->update();
    } else {
        $result = False;
    }
    echo json_encode($result);
    exit;
}

if (!empty($_POST['modSettings'])) {
    $name = htmlspecialchars($_POST['modSettings']);
    $option = htmlspecialchars($_POST['option']);
    $value = htmlspecialchars($_POST['value']);
    $op = htmlspecialchars($_POST['op']);

    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    if ($thisApp->isInstalled()) {
        $thisApp->get();
        $thisApp->$option = $value;
        if ($op == 'plugin') {
            $result = $thisApp->update();
        } else {
            $thisApp->time = $App::parseTime($thisApp->dayNb, $thisApp->dayName, $thisApp->hour);
            if ($thisApp->update()) {
                $result = $thisApp->time;
            } else {
                $result = false;
            }
        }
    } else {
        $result = False;
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Scheduled Tasks
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Modify cron job
if (!empty($_POST['mod_cron'])) {
    $cronName = $_POST['cron'];
    $option = $_POST['option'];
    $value = $_POST['value'];
    $CronJobs = new AppCron($db);
    $cron = $CronJobs->instantiate($cronName);
    if ($cron->isInstalled()) {
        $cron->get();
        $cron->$option = $value;
        $cron->time = AppCron::parseTime($cron->dayNb, $cron->dayName, $cron->hour);
        if ($cron->update()) {
            $result = $cron->time;
        } else {
            $result = false;
        }
    } else {
        $result = False;
    }

    echo json_encode($result);
    exit;
}

// Run cron job
if (!empty($_POST['run_cron'])) {
    $cronName = $_POST['cron'];
    $CronJobs = new AppCron($db);
    $cron = $CronJobs->instantiate($cronName);
    $result= $cron->run();
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Export tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Export db if asked
if (!empty($_POST['export'])) {
    $ref_id = htmlspecialchars($_POST['refid']);
    $ref = new DrawRef($db,$ref_id);
    $tablenames = $ref->get_tables();
    $result = exportdbtoxls($ref_id,$tablenames);
    echo json_encode($result);
    exit;
}

if (!empty($_POST['config_modify'])) {
    if ($AppConfig->update($_POST)) {
        $result['status'] = true;
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

if (!empty($_POST['delete_temp'])) {
	$path_to_file = $_SESSION['path_to_app'].htmlspecialchars($_POST['link']);
	if (unlink($path_to_file)) {
		$result = "deleted";
	} else {
		$result = "not_deleted";
	}
	echo json_encode($result);
    exit;
}

if (!empty($_POST['backup'])) {
    $op = $_POST['op'];
    if ($op === 'dbbackup') {
        include_once(PATH_TO_APP . '/cronjobs/Dbbackup.php');
        $job = new DbBackup($db);
    } else {
        include_once(PATH_TO_APP . '/cronjobs/FullBackup.php');
        $job = new FullBackup($db);
    }
    $result = $job->run();
    echo json_encode($result);
    exit;
}

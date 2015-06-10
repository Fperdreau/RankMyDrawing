<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 22/11/14
 * Time: 12:19
 */

// Includes required files (classes)
require_once('../includes/includes.php');

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Login
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Check login
if (!empty($_POST['login'])) {
    $user = new Users($db);

    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $result = "nothing";
    if ($user -> get($username) == true) {
        if ($user -> check_pwd($password) == true) {
            $_SESSION['logok'] = true;
            $_SESSION['username'] = $user -> username;
            $result = "logok";
        } else {
            $_SESSION['logok'] = false;
            $result = "wrong_password";
        }
    } else {
        $result = "wrong_username";
    }
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
        $username = $db_set ->getinfo($users_table,'username',array("email"),array("'$email'"));
        $user->get($username);
        $reset_url = $AppMail->site_url."index.php?page=renew_pwd&hash=$user->hash&email=$user->email";
        $subject = "Change password";
        $content = "
            Hello $user->firstname $user->lastname,<br>
            <p>You requested us to change your password.</p>
            <p>To reset your password, click on this link:
            <br><a href='$reset_url'>$reset_url</a></p>
            <br>
            <p>If you did not request this change, please ignore this email.</p>
            The Journal Club Team
            ";

        $body = $AppMail -> formatmail($content);
        if ($AppMail->send_mail($email,$subject,$body)) {
            $result = "sent";
        } else {
            $result = "not_sent";
        }
    } else {
        $result = "wrong_email";
    }
    echo json_encode($result);
    exit;
}

// Change user password after confirmation
if (!empty($_POST['conf_changepw'])) {
    $username = htmlspecialchars($_POST['username']);
    $oldpassword = htmlspecialchars($_POST['oldpassword']);
    $password = htmlspecialchars($_POST['password']);

	$user = new Users($db,$username);
	if ($user->check_pwd($oldpassword)) {
	    $crypt_pwd = $user->crypt_pwd($password);
	    $db->updatecontent($db->tablesname['Users'],array("password"=>$crypt_pwd),array("username"=>$username));
	    $result = "changed";
	} else {
		$result = "wrong";
	}

    echo json_encode($result);
    exit;
}

// Process user modifications
if (!empty($_POST['mod_admininfo'])) {
    $user = new Users($db,$_POST['username']);
    if ($user -> updateuserinfo($_POST)) {
        $result = "<p id='success'>The modification has been made!</p>";
    } else {
        $result = "<p id='warning'>Something went wrong!</p>";
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Experiment settings
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Show ref drawings settings
if(!empty($_POST['select_ref'])) {
    $ref = $_POST['refid'];
    $result = showrefsettings($ref);
    echo json_encode($result);
    exit;
}

// Modify ref drawing settings
if (!empty($_POST['mod_ref_params'])) {
    $ref = new DrawRef($db,$_POST['refid']);
    $elo = $_POST['initial_score'];
    $pair = $_POST['nb_pairs'];
    $filter = $_POST['filter'];
    $status = $_POST['status'];

    if ($pair > $ref->max_nb_pairs) {
        $result = "<p id='warning'>The chosen number of pairs exceeds the maximum possible number of pairs</p>";
    } else {
        $ref->update($_POST);
        $result = "<p id='success'>Modifications have been successfully made</p>";
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
    $refid = $_POST['refid'];
    $ref = new DrawRef($db);
    $result = $ref->exists($refid);
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
    $img = "../images/$item->refid/img/$item->filename";
    $result['content'] = "
        <div class='item_img' style='background: url($img) no-repeat; background-size: 600px;'>
            <div class='item_caps'>
                <div id='item_title'>$item->file_id</div>
                <span style='color:#CF5151; font-weight: bold;'>Score: </span>$item->score<br>
                <span style='color:#CF5151; font-weight: bold;'>Number of matchs: </span>$item->nb_occ<br>
                <span style='color:#CF5151; font-weight: bold;'>Number of win: </span>$item->nb_win<br>
            </div>
        </div>
        <div class='del_item' data-ref='$item->refid' data-item='$item->file_id'>Delete</div>
        ";
    $result['item'] = $itemid;
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
Export tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Export db if asked
if (!empty($_POST['export'])) {
    $ref_id = htmlspecialchars($_POST['refid']);
    $ref = new DrawRef($db,$ref_id);
    $tablenames = $ref->get_tables();
    $result = '';
    foreach ($tablenames as $table_name) {
        $result = exportdbtoxls($table_name);
    }
    echo json_encode($result);
    exit;
}

if (!empty($_POST['config_modify'])) {
    $AppConfig->update($_POST);
    $result = "<p id='success'>Modifications have been made!</p>";
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
        include_once(PATH_TO_APP.'/cronjobs/Dbbackup.php');
        $job = new DbBackup($db);
    } else {
        include_once(PATH_TO_APP.'/cronjobs/FullBackup.php');
        $job = new FullBackup($db);
    }
    $result = $job->run();
    echo json_encode($result);
    exit;
}

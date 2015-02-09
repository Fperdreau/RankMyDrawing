<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 22/11/14
 * Time: 12:19
 */

session_start();
// Includes required files (classes)
include_once($_SESSION['path_to_includes'].'includes.php');
include_once($_SESSION['path_to_app'].'admin/includes/includes.php');

$db_set = new DB_set();

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Login
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Check login
if (!empty($_POST['login'])) {
    $user = new users();

    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $result = "nothing";
    if ($user -> getuserinfo($username) == true) {
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
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Admin information
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Send password change request if email exists in database
if (!empty($_POST['change_pw'])) {
    $email = htmlspecialchars($_POST['email']);
    $user = new users();
    $mail = new myMail();

    if ($user->mail_exist($email)) {
        $username = $db_set ->getinfo($users_table,'username',array("email"),array("'$email'"));
        $user->getuserinfo($username);
        $reset_url = $mail->site_url."index.php?page=renew_pwd&hash=$user->hash&email=$user->email";
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

        $body = $mail -> formatmail($content);
        if ($mail->send_mail($email,$subject,$body)) {
            $result = "sent";
        } else {
            $result = "not_sent";
        }
    } else {
        $result = "wrong_email";
    }
    echo json_encode($result);
}

// Change user password after confirmation
if (!empty($_POST['conf_changepw'])) {
    $username = htmlspecialchars($_POST['username']);
    $oldpassword = htmlspecialchars($_POST['oldpassword']);
    $password = htmlspecialchars($_POST['password']);

	$user = new users($username);
	if ($user->check_pwd($oldpassword)) {
		$db_set = new DB_set();
	    $crypt_pwd = $user->crypt_pwd($password);
	    $db_set->updatecontent($users_table,"password","'$crypt_pwd'",array("username"),array("'$username'"));
	    $result = "changed";
	} else {
		$result = "wrong";
	}

    echo json_encode($result);

}

// Process user modifications
if (!empty($_POST['mod_admininfo'])) {
    $user = new users($_POST['username']);
    if ($user -> updateuserinfo($_POST)) {
        $result = "<p id='success'>The modification has been made!</p>";
    } else {
        $result = "<p id='warning'>Something went wrong!</p>";
    }
    echo json_encode($result);
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Experiment settings
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Show ref drawings settings
if(!empty($_POST['select_ref'])) {
    $ref = $_POST['refid'];
    $result = showrefsettings($ref);
    echo json_encode($result);
}

// Modify ref drawing settings
if (!empty($_POST['mod_ref_params'])) {
    $ref = new DrawRef($_POST['refid']);
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
}

// Display ref drawing content (instruction/consent form)
if (!empty($_POST['display_content'])) {
    $ref_id = $_POST['refid'];
    $lang = $_POST['lang'];
    $ref = new DrawRef($ref_id);
    $inst = $ref->get_content("instruction");
    $cons = $ref->get_content("consent");
    $result = array(
        'instruction' => $inst["$lang"],
        'consent' => $cons["$lang"]);
    echo json_encode($result);
}

// Load text area with content to modify
if (!empty($_POST['cha_content'])) {
    $lang = htmlspecialchars($_POST['lang']);
    $ref_id = htmlspecialchars($_POST['refid']);
    $type = htmlspecialchars($_POST['type']);
    $ref = new DrawRef($ref_id);
    $content = $ref->get_content($type);
    $result = $content["$lang"];
    echo json_encode($result);
}

// Add instruction
if (!empty($_POST['add_content'])) {
    $lang = htmlspecialchars($_POST['lang']);
    $ref_id = htmlspecialchars($_POST['refid']);
    $instruction = mysqli_real_escape_string($db_set->bdd,$_POST['instruction']);
    $consent = mysqli_real_escape_string($db_set->bdd,$_POST['consent']);
    $table = $db_set->dbprefix.$ref_id."_content";
    $result = $db_set -> addcontent($table,"type,lang,content","'instruction','$lang','$instruction'");
    $result = $db_set -> addcontent($table,"type,lang,content","'consent','$lang','$consent'");
    echo json_encode($result);
}

// Modify instruction
if (!empty($_POST['mod_content'])) {
    $lang = $_POST['lang'];
    $ref_id = $_POST['refid'];
    $type = $_POST['type'];
    $content = mysql_real_escape_string($_POST['content']);

    $ref = new DrawRef($ref_id);
    $table = $db_set->dbprefix.$ref->file_id.'_content';
    $result = $db_set -> updatecontent($table,'content',"'$content'",array('type','lang'),array("'$type'","'$lang'"));
    echo json_encode($result);
}

// Delete instruction
if (!empty($_POST['del_content'])) {
    $ref_id = $_POST['refid'];
    $lang = $_POST['lang'];
    $type = $_POST['type'];
    $db_set -> deletecontent($content_table,array('type','lang','file_id'),array("'$type'","'$lang'","'$ref_id'"));
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Drawing Management
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Check availability of the new reference drawing's label
if (!empty($_POST['check_availability'])) {
    $refid = $_POST['refid'];
    $ref = new DrawRef();
    $result = $ref->exists($refid);
    echo json_encode($result);
}

// Sort items
if (!empty($_POST['sortitems'])) {
    $filter = htmlspecialchars($_POST['sortitems']);
    $refdraw = htmlspecialchars($_POST['refdraw']);
    $ref = new DrawRef($refdraw);
    if ($filter == "") {
        $filter = null;
    }
    $result = $ref->displayitems($filter);
    echo json_encode($result);
}

// Delete reference drawing
if (!empty($_POST['deleteref'])) {
    $file_id = htmlspecialchars($_POST['deleteref']);
    $ref = new DrawRef($file_id);
    $result = $ref->delete();
    echo json_encode($result);
}

// Show item description
if(!empty($_POST['show_item'])) {
    $refid = $_POST['refid'];
    $itemid = $_POST['itemid'];
    $item = new ELO($refid,$itemid);

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
}

// Delete item drawing
if (!empty($_POST['delete_item'])) {
    $item_id = htmlspecialchars($_POST['delete_item']);
    $drawref = htmlspecialchars($_POST['drawref']);

    $ref = new Elo($drawref,$item_id);
    $result = $ref->delete();
    echo json_encode($result);
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Export tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Export db if asked
if (!empty($_POST['export'])) {
    $ref_id = htmlspecialchars($_POST['refid']);
    $ref = new DrawRef($ref_id);
    $tablenames = $ref->get_tables();
    foreach ($tablenames as $table_name) {
        $result = exportdbtoxls($table_name);
    }
    echo json_encode($result);
}

if (!empty($_POST['config_modify'])) {
    $config = new site_config('get');
    $config->update_config($_POST);
    $result = "<p id='success'>Modifications have been made!</p>";
    echo json_encode($result);
}

if (!empty($_POST['delete_temp'])) {
	$path_to_file = $_SESSION['path_to_app'].htmlspecialchars($_POST['link']);
	if (unlink($path_to_file)) {
		$result = "deleted";
	} else {
		$result = "not_deleted";
	}
	echo json_encode($result);
}

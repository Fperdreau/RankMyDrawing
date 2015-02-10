<?php
@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

# Classes
$config = new site_config();
$db_set = new DB_set();

$lang = $_SESSION['lang'];

// Store visitor information
$user = new participant();

// If possible, select an experiment in which he/she has not participated
$refdrawlist = get_refdrawinglist("file_id");
$nrefdraw = count($refdrawlist);
$ip = $user -> getip();
$drawref = selectdrawref($ip);

if ($drawref != NULL && $nrefdraw > 0) {
    $time = time();
    $msg = "";

    $_SESSION['time_start'] = $time;
    $_SESSION['pair'] = 1;
    $_SESSION['drawref'] = $drawref;

    $user->create_user($_SESSION,$db_prefix.$drawref);
    $_SESSION['id'] = $user->hash;

    $user->write_sql($db_prefix.$drawref,$id,'time_start',$time);
    $ok = 1;
} else {
    $full = 1;
    $ok = 0;
    $www = $_SESSION['path_to_pages']."pages/logout.php?full=$full";
    header("refresh: 3; URL=$www");
}

$result = "
    <div id='content'>
        <form class='sub_info'>
        <div class='feedback'></div>
        <label for='name'>Initials</label><input type='text' name='name' value='$name' id='name'><br>
        <label for='email'>Email</label><input type='text' name='email' value='$email' id='email'/><br>
        <label for='age'>Age</label><input type='text' name='age' value='$age' id='age' /><br>
        <label for='gender'>Gender</label><select name='gender' id='gender'>
                <option value='$gender' selected='selected'></option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option></select><br>
        <label for='drawlvl'>Drawing level</label><select name='drawlvl' id='drawlvl'>
                <option value='$drawlvl' selected='selected'></option>
                <option value='Low'>Low</option>
                <option value='Medium'>Medium</option>
                <option value='Good'>Good</option>
                <option value='Expert'>Expert</option>
                </select><br>
        <label for='artint'>Interested in visual arts</label><select name='artint' id='artint'>
                <option value='$artint' selected='selected'></option>
                <option value='No'>No</option>
                <option value='Yes'>Yes</option></select><br>
        <input type='submit' name='Submit' >
        </form>
    </div>
    ";

echo json_encode($result);

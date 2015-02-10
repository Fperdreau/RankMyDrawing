<?php
/**
 * User: Florian Perdreau
 * Date: 20/09/2014
 * Time: 10:18
 */

@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

# Classes
$config = new site_config('get');
$db_set = new DB_set();
$ref = new DrawRef();
$user = new participant();
$ip = $user->getip();

// Check if there are available experiments
$ref_id = $ref->selectdrawref($ip);
if (false !== $ref_id) {
    $ref = new DrawRef($ref_id);

    // Get languages
    $languages = $ref->getlanguages();
    $langoption = "<option value='' selected></option>";
    foreach ($languages as $lang) {
        $langoption .= "<option value='$lang'>$lang</option>";
    }
}

# Is the experiment ON?
if ($config->expon == 'on' && $ref_id !== false) {
    $content = "
    <form id='user_info'>
        <div class='feedback'></div>
        <label for='name' class='label'>Initials</label>
            <input type='text' name='name' value='$user->name' id='user_name'><br>
        <label for='email' class='label'>Email</label>
            <input type='text' name='email' value='$user->email' id='user_email'/><br>
        <label for='age' class='label'>Age</label>
            <input type='text' name='age' value='$user->age' id='user_age' /><br>
        <label for='gender' class='label'>Gender</label>
            <select name='gender' id='user_gender'>
                <option value='$user->gender' selected='selected'></option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
            </select><br>
        <label for='drawlvl' class='label'>Drawing level</label>
            <select name='drawlvl' id='user_drawlvl'>
                <option value='$user->drawlvl' selected='selected'></option>
                <option value='Low'>Low</option>
                <option value='Medium'>Medium</option>
                <option value='Good'>Good</option>
                <option value='Expert'>Expert</option>
            </select><br>
        <label for='language' class='label'>Language</label>
            <select name='language' id='user_language'>
            $langoption
            </select><br>
        <label for='artint' class='label'>Interested in visual arts</label>
            <select name='artint' id='user_artint'>
                <option value='$user->artint' selected='selected'></option>
                <option value='No'>No</option>
                <option value='Yes'>Yes</option>
            </select><br>
        <p style='text-align: right'><input type='submit' name='Submit' id='submit' value='Next' class='user_form' data-ref='$ref_id'></p>
    </form>";
} else {
    $content = '<p class="sectioname" style="color:#880000;">Sorry, either the website is under maintenance or there is currently no experiment in progress.</p>
        <p class="sectioname" style="color:#880000;">D&eacute;sol&eacute;, soit le site est en maintenance ou il n\'y a actuellement aucune exp&eacute;rience en cours.</p>';
}

$result = "
    <div id='content'>
        <span id='pagename'>Welcome to RankMyDrawings</span>
        <div class='user_info'>
        $content
        </div>
    </div>
";

echo json_encode($result);

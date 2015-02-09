<?php
@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

check_login();
$user = new users($_SESSION['username']);
$result = "
    <div id='content'>
    <span id='pagename'>Admin Information</span>
        <form method='post' action=''>
            <label for='username' class='label'>Username</label><input type='text' name='username' id='ch_username' value='$user->username'/><br>
            <label for='email' class='label'>Email</label><input type='text' name='email' id='ch_email' value='$user->email'/><br>
            <p style='text-align: right'><input type='submit' value='Apply' name='submit' id='submit' class='change_admininfo'/></p>
            
            <input type='button' value='Modify password' class='change_pwd' id='submit' />         
            <div class='change_pwd_form' style='display: none;'>
                <label for='ch_oldpassword' class='label'>Old Password</label><input type='password' id='ch_oldpassword' name='ch_oldpassword' value=''/><br>
            	<label for='new_password' class='label'>New Password</label><input type='password' id='ch_password' name='new_password' value=''/></br>
            	<label for='new_conf_password' class='label'>Confirm new Password</label><input type='password' id='ch_conf_password' name='new_password' value=''/>				
	            <p style='text-align: left'><input type='submit' name='submit' id='submit' value='Modify' class='conf_changepw'/></p>
            </div>
        </form>
        <div class='feedback'></div>
        <br/>
    </div>
";

echo json_encode($result);

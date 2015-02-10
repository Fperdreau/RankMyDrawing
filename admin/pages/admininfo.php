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

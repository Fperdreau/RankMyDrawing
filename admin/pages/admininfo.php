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

require_once('../includes/includes.php');

check_login();
$user = new Users($db,$_SESSION['username']);
$result = "
    <section>
    <h2>Admin Information</h2>
        <form id='admin_info'>
            <input type='hidden' name='mod_admininfo' value='true'/>
            <div class='formcontrol'>
                <label for='username' class='label'>Username</label>
                <input type='text' name='username' value='$user->username'/>
            </div>
            <div class='formcontrol'>
                <label for='email' class='label'>Email</label>
                <input type='email' name='email' value='$user->email'/>
            </div>
            <div class='submit_btns'>
               <input type='submit' value='Apply' name='submit' class='processform'/>
            </div>
        </form>

        <form id='admin_pwd'>
            <input type='hidden' name='conf_changepw' value='true' />
            <input type='hidden' name='username' value='$user->username' />
            <input type='button' value='Modify password' class='change_pwd' /><br>
            <div class='change_pwd_form' style='display: none;'>
                <div class='formcontrol'>
                    <label for='old_password' class='label'>Old Password</label>
                    <input type='password' name='old_password' value='' required/>
                </div>
                <div class='formcontrol'>
            	    <label for='new_password' class='label'>New Password</label>
            	    <input type='password' name='password' value='' required/>
                </div>
                <div class='formcontrol'>
            	    <label for='new_conf_password' class='label'>Confirm new Password</label>
            	    <input type='password' name='conf_password' value='' required/>
                </div>
	            <p style='text-align: left'><input type='submit' name='submit' value='Modify' class='processform'/></p>
            </div>
        </form>
        <div class='feedback'></div>
        <br/>
    </section>
";

echo json_encode($result);

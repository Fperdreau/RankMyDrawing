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
echo "

<div id='modal' class='modalContainer' style='display:none;'>
    <section class='popupBody' style='display:inline-block'>
        <div class='popupHeader'></div>

        <div class='modal_section' id='user_login' data-title='Sign In'>
            <form id='login_form'>
                <input type='hidden' name='login' value='true'/>
                <div class='formcontrol' style='width: 100%;'>
                    <label for='log_username'>Username</label>
                    <input type='text' id='log_username' name='username'>
                </div>
                <div class='formcontrol' style='width: 100%;'>
                    <label for='log_password'>Password</label>
                    <input type='password' id='log_password' name='password'>
                </div>
                <div class='action_btns'>
                    <input type='submit' id='submit' value='Log In' class='login'/>
                </div>
            </form>
            <div class='forgot_password'><a href='' class='modal_trigger_changepw'>I forgot my password</a></div>
        </div>

        <div class='modal_section' id='user_changepw'>
            <div class='formcontrol' style='width: 100%;'>
                <label for='ch_email'>Email</label>
                <input type='text' id='ch_email' name='ch_email' placeholder='Your email'>
            </div>
            <div class='action_btns'>
                <div class='one_half'><a href='' class='btn back_btn'><i class='fa fa-angle-double-left'></i> Back</a></div>
                <div class='one_half last'><a href='' class='btn btn_red' id='modal_change_pwd'>Change</a></div>
            </div>
        </div>

        <div class='modal_section' id='item_settings'></div>

        <div class='feedback'></div>
        <div class='modal_close'></div>
    </section>
</div>

<div id='item_modal' class='modalContainer' style='display:none;'>
    <section class='popupBody' style='display:inline-block'>
        <div class='modal_section' id='item_description'></div>
        <div class='modal_close'></div>
    </section>
</div>
";



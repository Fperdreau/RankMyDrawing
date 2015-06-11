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

<div id='modal' class='popupContainer' style='display:none;'>
 <header class='popupHeader'>
 <span class='header_title'>Login</span>
 <span class='modal_close'><i class='fa fa-times'></i></span>
 </header>

    <section class='popupBody'>

        <div class='modal_section' id='user_login'>
            <form id='login_form'>
                <div class='formcontrol' style='width: 100%;'>
                    <label for='log_username'>Username</label>
                    <input type='text' id='log_username' name='username'>
                </div>
                <div class='formcontrol' style='width: 100%;'>
                    <label for='log_password'>Password</label>
                    <input type='password' id='log_password' name='password'>
                </div>
                <div class='action_btns'>
                    <div class='one_half'>
                        <input type='submit' id='submit' value='Log In' class='login'/>
                    </div>
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

        <div class='feedback'></div>
    </section>
</div>

<div id='item_modal' class='item_popupContainer' style='display:none;'>
 <header class='popupHeader'>
 <span class='header_title'>Description</span>
 <span class='modal_close'><i class='fa fa-times'></i></span>
 </header>

    <section class='popupBody'>

        <div class='modal_section' id='item_description'></div>

        <div class='feedback'></div>
    </section>
</div>
";



<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 24/11/14
 * Time: 17:54
 */
echo "

<div id='modal' class='popupContainer' style='display:none;'>
 <header class='popupHeader'>
 <span class='header_title'>Login</span>
 <span class='modal_close'><i class='fa fa-times'></i></span>
 </header>

    <section class='popupBody'>

        <div class='user_login'>
            <form>
            <label for='log_username'>Username</label><input type='text' id='log_username' name='log_username' value=''/>
            <label for='log_password'>Password</label><input type='password' id='log_password' name='log_password' value=''/>
            <div class='action_btns'>
                <div class='one_half'><input type='submit' value='Log in' class='login' id='submit' /></div>
            </div>
            </form>
            <div class='forgot_password'><a href='' class='modal_trigger_changepw'>I forgot my password</a></div>
        </div>

        <div class='user_changepw'>
            <label for='ch_email'>Email</label><input type='text' id='ch_email' name='ch_email' value=''/></br>
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

        <div class='item_description'></div>

        <div class='feedback'></div>
    </section>
</div>
";



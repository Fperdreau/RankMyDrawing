<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 24/11/14
 * Time: 17:54
 */
echo "

<div id='modal' class='contact_container' style='display:none;'>
 <header class='popupHeader'>
 <span class='header_title'>Login</span>
 <span class='modal_close'><i class='fa fa-times'></i></span>
 </header>

    <section class='popupBody'>

        <div class='send_msg'>
            <form method='post' action='' class='form' id='contact_form'>
                <label for='name'>Your name</label><input type='text' name='name' id='contact_name' value='Your name'><br>
                <label for='mail'>E-mail</label><input type='text' name='mail' id='contact_mail' value='Your email'><br>
                <label for='message'>Message</label><br>
                <textarea id='message' name='message' rows='10' cols='50'>Your message</textarea><br>
                <p style='text-align: right;'><input type='submit' name='send' value='Send' id='submit' class='contact_send'></p>
            </form>
        </div>

        <div class='feedback'></div>
    </section>
</div>

    ";



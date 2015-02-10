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



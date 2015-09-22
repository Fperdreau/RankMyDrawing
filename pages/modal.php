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

        <!-- Sign in section -->
        <div class='modal_section' id='send_msg' data-title='Send a message'>
            <form method='post' action='' class='form' id='contact_form'>
                <label for='name'>Your name</label><input type='text' name='name' id='contact_name' placeholder='Your name'><br>
                <label for='mail'>E-mail</label><input type='text' name='mail' id='contact_mail' placeholder='Your email'><br>
                <label for='message'>Message</label><br>
                <textarea id='message' name='message' rows='10' cols='50' placeholder='Your message'></textarea><br>
                <p style='text-align: right;'><input type='submit' name='send' value='Send' id='submit' class='contact_send'></p>
            </form>
        </div>

        <div class='feedback'></div>
    </section>
</div>

    ";



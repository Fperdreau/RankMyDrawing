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
        <div class='modal_section' id='contact' data-title='Send a message'>
            <form id='contact_form'>
                <div class='formcontrol'>
                    <input type='text' name='name' placeholder='Your name' required'>
                </div>
                <div class='formcontrol'>
                    <input type='email' name='mail' placeholder='Your email' required>
                </div>
                <div class='formcontrol'>
                    <textarea id='message' name='message' placeholder='Your message' required></textarea>
                </div>
                <div class='submit_btns'>
                    <input type='submit' name='send' value='Send' class='contact_send'>
                </div>

            </form>
        </div>

        <div class='feedback'></div>
        <div class='modal_close'></div>
    </section>
</div>
    ";



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

require_once('../includes/boot.php');

$ref = new DrawRef($db);
$user = new Participant($db);
$ip = $user->getip();

// Check if there are available experiments
$ref_id = $ref->selectdrawref($ip);
if (false !== $ref_id) {
    $ref = new DrawRef($db, $ref_id);

    // Get languages
    $languages = $ref->getlanguages();
    $langoption = "<option value='' selected></option>";
    foreach ($languages as $lang) {
        $langoption .= "<option value='$lang'>$lang</option>";
    }
}

# Is the experiment ON?
if ($AppConfig->expon == 'on' && $ref_id !== false) {
    $content = "
    <form id='user_info'>
        <div class='feedback'></div>
        <div class='formcontrol'>
            <label for='name' class='label'>Initials</label>
            <input type='text' name='name' value='$user->name' id='user_name' required/>
        </div>

        <div class='formcontrol'>
            <label for='email' class='label'>Email</label>
            <input type='email' name='email' value='$user->email' id='user_email' required/>
        </div>

        <div class='formcontrol'>
            <label for='age' class='label'>Age</label>
            <input type='text' name='age' value='$user->age' id='user_age' required maxlength='3'/>
        </div>

        <div class='formcontrol'>
            <label for='gender' class='label'>Gender</label>
            <select name='gender' id='user_gender' required>
                <option value='$user->gender' selected='selected'></option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
            </select>
        </div>

        <div class='formcontrol'>
            <label for='drawlvl' class='label'>Drawing level</label>
            <select name='drawlvl' id='user_drawlvl' required>
                <option value='$user->drawlvl' selected='selected'></option>
                <option value='Low'>Low</option>
                <option value='Medium'>Medium</option>
                <option value='Good'>Good</option>
                <option value='Expert'>Expert</option>
            </select>
        </div>

        <div class='formcontrol'>
            <label for='language' class='label'>Language</label>
            <select name='language' id='user_language' required>
            $langoption
            </select>
        </div>

        <div class='formcontrol'>
            <label for='artint' class='label'>Interested in visual arts</label>
            <select name='artint' id='user_artint' required>
                <option value='$user->artint' selected='selected'></option>
                <option value='No'>No</option>
                <option value='Yes'>Yes</option>
            </select>
        </div>
        <input type='hidden' name='add_user' value='true' />
        <input type='hidden' name='refid' value='$ref_id' />
        <div class='submit_btns'>
            <input type='submit' name='Submit' value='Next' class='user_form'/>
        </div>
    </form>";
} else {
    $content = '<p class="sectioname" style="color:#880000;">Sorry, either the website is under maintenance or there is currently no experiment in progress.</p>
        <p class="sectioname" style="color:#880000;">D&eacute;sol&eacute;, soit le site est en maintenance ou il n\'y a actuellement aucune exp&eacute;rience en cours.</p>';
}

$result = "
    <section class='user_info'>
        <h2>Information</h2>
        $content
    </section>
";

echo json_encode($result);

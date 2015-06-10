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

// Declare classes
$user = new Users($db);
$user->get($_SESSION['username']);

// Make hours options list
$start = "07:00";
$end = "20:00";

$tStart = strtotime($start);
$tEnd = strtotime($end);
$tNow = $tStart;
$timeopt = "";
while($tNow <= $tEnd){
    $opt =  date("H:i",$tNow);
    $timeopt .= "<option value='$opt'>$opt</option>";
    $tNow = strtotime('+30 minutes',$tNow);
}

$result = "
<div id='content'>
    <span id='pagename'>Configuration</span>
    <div class='section_header'>Site parameters</div>
    <div class='section_content'>
        <form method='post' action='' class='form' id='config_form_site'>
            <div class='feedback_site'></div>
            <input type='hidden' name='config_modify' value='true'/>
            <label for='sitetitle' class='label'>Site title</label><input type='text' size='30' name='sitetitle' value='$AppConfig->sitetitle' /><br>
            <label for='clean_day' class='label'>Oldest DB backups to keep (in days)</label><input type='text' size='30' name='clean_day' value='$AppConfig->clean_day' />
            <p style='text-align: right'><input type='submit' name='modify' value='Modify' id='submit' class='config_form_site'/></p>
        </form>
    </div>

    <div class='section_header'>General Settings</div>
    <div class='section_content'>
        <form method='post' action='' class='form' id='config_form_exp'>
            <div class='feedback_exp'></div>
            <input type='hidden' name='config_modify' value='true'/>
            <label for='expon' class='label'>Site status</label>
            <select name='expon'>
                <option value='$AppConfig->expon' selected>$AppConfig->expon</option>
                <option value='on'>on</option>
                <option value='off'>off</option>
                </select><br>
            <label for='redirecturl' class='label'>Redirect Url</label><input type='text' name='redirecturl' value='$AppConfig->redirecturl' />
            <p style='text-align: right'><input type='submit' value='Modify' id='submit' class='config_form_exp'></p>
       </form>
    </div>

    <div class='section_header'>Email host information</div>
    <div class='section_content'>
        <form method='post' action='' class='form' id='config_form_mail'>
            <div class='feedback_mail'></div>
            <input type='hidden' name='config_modify' value='true'/>
            <label for='mail_from' class='label'>Sender Email address</label><input name='mail_from' type='text' value='$AppConfig->mail_from'></br>
            <label for='mail_from_name' class='label'>Sender name</label><input name='mail_from_name' type='text' value='$AppConfig->mail_from_name'></br>
            <label for='mail_host' class='label'>Email host</label><input name='mail_host' type='text' value='$AppConfig->mail_host'></br>
            <label for='SMTP_secure' class='label'>SMTP access</label>
                <select name='SMTP_secure'>
                    <option value='$AppConfig->SMTP_secure' selected='selected'>$AppConfig->SMTP_secure</option>
                    <option value='ssl'>ssl</option>
                    <option value='tls'>tls</option>
                    <option value='none'>none</option>
                 </select><br>
            <label for='mail_port' class='label'>Email port</label><input name='mail_port' type='text' value='$AppConfig->mail_port'></br>
            <label for='mail_username' class='label'>Email username</label><input name='mail_username' type='text' value='$AppConfig->mail_username'></br>
            <label for='mail_password' class='label'>Email password</label><input name='mail_password' type='password' value='$AppConfig->mail_password'></br>
            <label for='pre_header' class='label'>Email header prefix</label><input name='pre_header' type='text' value='$AppConfig->pre_header'>
            <p style='text-align: right'><input type='submit' name='modify' value='Modify' id='submit' class='config_form_mail'/></p>
        </form>
    </div>
</div>

";
echo json_encode($result);

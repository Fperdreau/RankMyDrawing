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

// Get reference drawing list
$ref = new DrawRef($db);
$refdrawlist = $ref->get_refdrawinglist("file_id");
$optioncontent = "";
foreach ($refdrawlist as $cur_refdraw) {
	$optioncontent .= "<option name='$cur_refdraw'>$cur_refdraw</option>";
}

$result = "
    <div id='content'>
		<span id='pagename'>Admin tools</span>
        <div class='section_header'>Tools</div>
        <div class='section_content'>
        	<div id='exportdb'>
                <label for='export'>Export Database in XLS</label>
                <select class='exportdb'>
                    <option value='' selected>Select a database</option>
                    $optioncontent
                </select>
            </div><br>

            <div id='dbbackup'>
            <label for='backup'>Backup databases</label>
            <input type='button' name='backup' value='Proceed' id='submit' class='backup' data-op='dbbackup'/>
            </div><br>

            <div id='fullbackup'>
            <label for='full_backup'>Full backup (all databases + files)</label>
            <input type='button' name='full_backup' value='Proceed' id='submit' class='backup' data-op='fullbackup'/>
            </div>

        </div>
    </div>";

echo json_encode($result);

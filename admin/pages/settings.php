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

$refdrawlist = $db -> getinfo($db->tablesname['DrawRef'],"file_id");
$options = "";
$content = "";
if (!empty($refdrawlist)) {
    foreach ($refdrawlist as $ref_id) {
        $options .= "<option value='$ref_id'>$ref_id</option>";
        $ref = new DrawRef($db,$ref_id);
        $content .= $ref->showSettings();
    }
} else {
    $content = "<p id='warning'>You must upload your reference drawings first<br>Go to the <a href='index.php?page=management'>Drawing Management section</a></p>";
}

$result = "
    <section>
        <h2>Experiment settings</h2>
        <div class='formcontrol'>
            <label for='select_ref'>Select a reference</label>
            <select name='select_ref' class='select_ref'>
                <option value='all' selected>All</option>
                $options
            </select>
        </div>
        <div class='all_ref_params'>$content</div>
    </section>";
echo json_encode($result);

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
$refdraw = new DrawRef($db);
$refdrawlist = $refdraw->get_refdrawinglist("file_id");
$nrefdraw = count($refdrawlist);
$imglist = "";
if (!empty($refdrawlist)) {
	foreach ($refdrawlist as $cur_ref) {
		$ref = new DrawRef($db,$cur_ref);
        $imglist .= $ref->showDetails();
	}
}

$result = "
	<div id='content'>
		<span id='pagename'>Drawing Management</span>
        <div class='refdraw-add' style='padding: 10px; background-color: #dddddd; margin: 10px auto 10px auto; height: 150px; width: 90%;'>
        	<div style='display: table-cell; width: 30%;'>
                <div style='display: block;'>
	        	  <label for='newref' class='label' style='width: auto;'>1. Choose a reference name</label>
                </div>
                <div style='display: block;'>
                    <form action='' id='newrefid'>
    	        		<input type='text' value='' name='newref' id='newref' />
    	        		<input type='submit' id='submit' class='newrefid' />
    	        	</form>
                </div>
        	</div>

            <!--<div class='refupload' style='text-align: center; width: 50%; margin-left: 100px;'>
                <div style='display: block;'>
                    <label class='label' style='width: auto;'>2. Upload the reference drawing</label>
                </div>
                <div class='ref_upl' style='display: block; margin-left: 20%;'>
                    <input type='file' name='ref' class='upload'>
                </div>
            </div>-->

            <div class='refupload' style='display: none; text-align: center; width: 50%; margin-left: 100px;'>
                <div style='display: block;'>
                    <label class='label' style='width: auto;'>2. Upload the reference drawing</label>
                </div>
                <div style='display: block; margin-left: 20%;'>
                    <div id='upref''></div>
                </div>
            </div>
            <div class='feedback'></div>
        </div>

        $imglist
    </div>
";

echo json_encode($result);

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
	<section>
		<h2>Add a reference drawing</h2>
        <div class='refdraw-add'>
        	<div style='display: table-cell; width: 30%;'>
                <div style='display: block;'>
	        	  <div class='newref_label'>1. Choose a name</div>
                </div>
                <div style='display: block;'>
                    <form id='newrefid'>
    	        		<input type='text' value='' name='newref' id='newref' />
    	        		<input type='submit' id='submit' class='newrefid' value='Add'/>
    	        	</form>
                </div>
        	</div>

            <div class='refupload' style='display: none; text-align: center; width: 50%; margin-left: 100px;'>
                <div class='newref_label'>2. Upload the reference drawing</div>
                <div style='margin: 0 20%;'>
                    <div id='upref'></div>
                </div>
            </div>
            <div class='feedback'></div>
        </div>
    </section>
    <div class='section_container'>
            $imglist
    </div>
";

echo json_encode($result);

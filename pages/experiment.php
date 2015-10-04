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

# Classes
$user = new Participant($db,$_SESSION['userid'],$_SESSION['refid']);
$ref = new DrawRef($db, $user->refid);
$exp = new Experiment($db, $ref,$user->userid);

// Get trial params
$trial = $exp->gettrial();
$pair = $_SESSION['pairslist'][$trial];

// Instantiate items
$item1 = new Ranking($db,$ref->file_id,$pair[0]);
$item2 = new Ranking($db,$ref->file_id,$pair[1]);

// Get corresponding files
$file1 = new Uploads($db,$item1->filename);
$file2 = new Uploads($db,$item2->filename);
$reffile = new Uploads($db,$ref->filename);

$img1 = "images/$ref->file_id/img/$file1->filename";
$img2 = "images/$ref->file_id/img/$file2->filename";
$original = "images/$ref->file_id/img/$reffile->filename";

$result = "
<div id='experiment_frame' data-user='$user->userid' data-ref='$ref->file_id'>
	<div class='original_container'>
		<div class='picture original' id='$ref->file_id'>
			<img src='$original' class='picture drawing_img'>
		</div>
	</div>
	<div class='img_container'>
		<div class='drawing' id='item1'>
			<img src='$img1' class='picture drawing_img' data-item='$item1->file_id' data-opp='$item2->file_id'>
		</div>
		<div class='picture drawing' id='item2'>
			<img src='$img2' class='picture drawing_img' data-item='$item2->file_id' data-opp='$item1->file_id'>
		</div>
	</div>
</div>
";

echo json_encode($result);


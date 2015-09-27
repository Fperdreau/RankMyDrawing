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
$user = new Participant($db, $_SESSION['userid'],$_SESSION['refid']);
$ref = new DrawRef($db, $user->refid);

// Get instructions
$content = $ref->get_content('instruction');
$instruction = $content[$user->language];
$exampleurl = 'images/example.png';

$result = "
<section>
    <h2>Instructions</h2>
    <div class='instructions'>$instruction</div>
    <div class='example'>
        <img src='$exampleurl'>
    </div>
	<div id='submit' class='start_btn' data-user='$user->userid' data-ref='$ref->file_id'>Start</div>
</section>
";

echo json_encode($result);

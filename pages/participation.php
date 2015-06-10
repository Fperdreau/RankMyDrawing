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

// Get previous settings
$content = $ref->get_content('consent');
$consent = $content[$user->language];

$result = "
<div id='content'>
    <span id='pagename'>Consent Form</span>
    <div class='section_content'>
        $consent
    </div>
    <div class='action_btn'>
        <div class='part_btn agree' id='agree'>I agree</div>
        <div class='part_btn decline' id='decline'>I decline</div>
    </div>
</div>
";

echo json_encode($result);

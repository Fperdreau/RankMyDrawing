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

// Page Header
$contact = "
    <span style='font-size: 16px; color: #FFFFFF;'>
    <a rel='leanModal' id='modal_trigger_contact' href='#modal' class='modal_trigger'>Contact</a>";

echo "
<div id='progressbar'>Progression: 0%</div>
<div class='header_container'>
    <div id='title'>
        <span id='sitetitle'>$AppConfig->sitetitle</span>
        <div id='welcome'>$contact</div>
    </div>
</div>
";

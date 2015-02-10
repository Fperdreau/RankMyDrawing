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
if (!isset($_SESSION['logok']) || !$_SESSION['logok']) {
    $showlogin = "
        <span style='font-size: 16px; color: #FFFFFF;'>
        <a rel='leanModal' id='modal_trigger_login' href='#modal' class='modal_trigger'>Log in</a>";
} else {
    $showlogin = "<span style='font-size: 16px;' id='logout'><a href=''>Log out</a></span>";
}

$config = new site_config('get');

echo "
<div class='displaymenu_btn' id='on'>.:: Menu ::.</div>
<div class='header_container'>
    <div id='title'>
        <span id='sitetitle'>$config->sitetitle</span>
        <div style='float: right; margin-right: 10px; margin-top: 20px; height: 20px;' id='welcome'>$showlogin</div>
    </div>
</div>
";

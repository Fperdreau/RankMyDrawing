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
@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');
require_once($_SESSION['path_to_app'].'admin/includes/includes.php');
require_once($_SESSION['path_to_app'].'admin/conf/config.php');

// Show selected ref drawing's settings
function showrefsettings($ref_id='all') {
    if ($ref_id == 'all') {
        $ref = new DrawRef();
        $refdrawlist = $ref->get_refdrawinglist();
        $result = "";
        foreach ($refdrawlist as $refid) {
            $result .= showrefsettings($refid);
        }
    } else {
        $ref = new DrawRef($ref_id);

        if ($ref->nb_draw > 0) {
            $ref->max_nb_pairs = factorial($ref->nb_draw)/(factorial($ref->nb_draw-2)*factorial(2));
        } else {
            $ref->max_nb_pairs = 0;
        }

        // Get previous settings
        $config = new site_config('get');
        $inst_langs = $ref->get_content('instruction');
        $cons_langs = $ref->get_content('consent');

        $option_content = "<select class='select_lang' id='$ref->file_id' data-type='instruction'>
            <option value='' selected></option>
            <option value='add' style='background-color: #dddddd;'>Add</option> ";
        $langs = array_keys($inst_langs);
        foreach ($langs as $lang) {
            $option_content .= "<option value='$lang'>$lang</option>";
        }
        $option_content .= "</select>";

        $result = "
        <div class='refdraw-param'>
            <div class='refdraw-header'>$ref->file_id</div>
            <div class='section_param'>
                <div class='feedback_params'></div>
                <form>
                <label for='elo' class='label'>Initial Elo score</label><input type='text' name='elo' id='elo_$ref->file_id' value='$ref->initial_score' />
                <span id='info'>Modifying this value will result in the recomputation of all items' score</span><br>
                <label for='pair' class='label'>Number of trials</label><input type='text' name='pair' id='pair_$ref->file_id' value='$ref->nb_pairs' />
                <span id='info'>Max number: $ref->max_nb_pairs</span><br>
                <label for='max_nb_users' class='label'>Max number of users</label><input type='text' name='max_nb_users' id='max_nb_users_$ref->file_id' value='$ref->max_nb_users' /><br>
                <label for='status' class='label'>Status</label>
                <select name='status' id='status_$ref->file_id' data-ref='$ref->file_id'>
                    <option value='$ref->status' selected>$ref->status</option>
                    <option value='on'>on</option>
                    <option value='off'>off</option>
                </select><br>
                <label for='filter' class='label'>Filter user</label>
                <select name='filter' id='filter_$ref->file_id' data-ref='$ref->file_id'>
                    <option value='$ref->filter' selected>$ref->filter</option>
                    <option value='on'>on</option>
                    <option value='off'>off</option>
                </select><br>
                <p style='text-align: right'><input type='submit' id='submit' class='mod_ref_params' data-ref='$ref->file_id'></p>
                </form>
            </div>

            <div class='section_param'>
                <div class='section_param-header'>Instructions & Consent form</div>
                <div>
                    <label for='lang' class='label'>Language</label>
                    $option_content
                </div>
                <div class='feedback_content'></div>
                <div class='lang_label'></div>
                <label class='label'>Instructions</label>
                <div class='instruction'></div>
                <label class='label'>Consent form</label>
                <div class='consent'></div>
                <div class='refdraw-submit-div'></div>
                </div>
        </div>
        ";
    }
    return $result;
}

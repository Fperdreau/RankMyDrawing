<?php
/**
 * File for Myuploads DRAG & DROP AREA
 *
 * PHP 5.2 or later
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of MyUploader.
 *
 * MyUploader is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MyUploader is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MyUploader.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Create drag & drop area
 * @param array $links
 * @return string
 */
function uploader($links=array(), $refid) {
    global $AppConfig;

    // Get files associated to this publication
    $filesList = "";
    if (!empty($links)) {
        foreach ($links as $fileid=>$info) {
            $filesList .=
                "<div class='upl_info' id='upl_$fileid'>
                <div class='upl_name' id='$fileid'>$fileid</div>
                <div class='del_upl' id='$fileid' data-upl='$fileid'>
                </div>
            </div>";
        }
    }

    $result = "
        <div class='upl_container'>
    	   <div class='upl_form'>
                <form method='post' enctype='multipart/form-data'>
                    <input type='file' name='ref,$refid' class='upl_input' multiple style='display: none;' />
                    <div class='upl_btn'>
                        Add Files
                        <br>(click or drop)
                        <div class='upl_filetypes'>($AppConfig->upl_types)</div>
                        <div class='upl_errors'></div>
                    </div>
                </form>
    	   </div>
            <div class='upl_filelist'>$filesList</div>
        </div>";
    return $result;
}
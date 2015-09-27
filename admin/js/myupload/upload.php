<?php
/**
 * File for Myuploads CSS style
 *
 * CSS 3.0
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
 * Upload script
 */
require_once('../../includes/includes.php');

$ref = new DrawRef($db);
$id = array_keys($_FILES);
$id = $id[0];
$data = explode(',',$id);
$op = $data[0];
$file_id = $data[1];
$filename = false;
if ($op == "ref") {
    $result = $ref->make($file_id,$_FILES[$id]);
} else {
    //Loop through each file
    $item = new Ranking($db);
    $result = $item->make($file_id,$_FILES[$id]);
}

$result['name'] = ($result['error'] == true) ? $result['status']:false;
echo json_encode($result);
exit;


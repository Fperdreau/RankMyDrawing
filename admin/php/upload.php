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

$ref = new DrawRef($db);
$id = array_keys($_FILES);
$id = $id[0];
$data = explode(',',$id);
$op = $data[0];
$file_id = $data[1];

$filename = false;
if ($op == "ref") {
    $filename = $ref->make($file_id,$_FILES[$id]);
} elseif ($op == "item") {
    //Loop through each file
    $item = new Ranking($db);
    $filename = $item->make($file_id,$_FILES[$id]);
}

if ($filename == false) {
    $result['status'] = false;
} elseif ($filename == "no_file") {
    $result['status'] = false;
    $result['msg'] ="no_file";
} elseif ($filename == "failed") {
    $result['status'] = false;
    $result['msg'] = "error";
} else {
    $result['status'] = true;
    $result['msg'] = $filename;
}
$result['refid'] = $file_id;
echo json_encode($result);
exit;

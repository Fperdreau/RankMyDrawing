<?php

require_once('../../includes/includes.php');

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
    $result['status'] ="no_file";
} elseif ($filename == "failed") {
	$result['status'] = "error";
} else {
	$result['status'] = $filename;
}
echo json_encode($result);
exit;


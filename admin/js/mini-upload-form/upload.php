<?php
session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

$ref = new DrawRef();
$id = array_keys($_FILES);
$id = $id[0];
$data = explode(',',$id);
$op = $data[0];
$file_id = $data[1];
if ($op == "ref") {
    $filename = $ref->make($file_id,$_FILES[$id]);
} elseif ($op == "item") {
    //Loop through each file
    $item = new Elo();
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


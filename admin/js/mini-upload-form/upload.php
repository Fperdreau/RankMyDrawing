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

if ($filename == "no_file") {
    echo '{"status":"no_file"}';	
} elseif ($filename == "failed") {
	echo '{"status":"error"}';	
} else {
	echo '{"status":"'.$filename.'"}';	
}
exit;


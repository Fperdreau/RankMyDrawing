<?php
@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

# Classes
$user = new participant($_SESSION['userid'],$_SESSION['refid']);
$ref = new DrawRef($user->refid);
$exp = new Experiment($ref,$user->userid);

// Get trial params
$trial = $exp->gettrial();
$pair = $_SESSION['pairslist'][$trial];

$item1 = new ELO($ref->file_id,$pair[0]);
$item2 = new ELO($ref->file_id,$pair[1]);

$img1 = "images/$ref->file_id/img/$item1->filename";
$img2 = "images/$ref->file_id/img/$item2->filename";
$original = "images/$ref->file_id/img/$ref->filename";

$result = "
<div id='experiment_frame' data-user='$user->userid'>
	<div class='original_container'>
		<div class='original' id='$ref->file_id'>
			<img src='$original' class='drawing_img'>
		</div>
	</div>
	<div class='img_container'>
		<div class='drawing' id='item1'>
			<img src='$img1' class='drawing_img' data-item='$item1->file_id' data-opp='$item2->file_id'>
		</div>
		<div class='drawing' id='item2'>
			<img src='$img2' class='drawing_img' data-item='$item2->file_id' data-opp='$item1->file_id'>
		</div>
	</div>
</div>
";

echo json_encode($result);


<?php

@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

# Classes
$user = new participant($_SESSION['userid'],$_SESSION['refid']);
$ref = new DrawRef($user->refid);

// Get instructions
$content = $ref->get_content('instruction');
$instruction = $content[$user->language];
$exampleurl = 'images/example.png';

$result = "
<div id='content'>
    <span id='pagename'>Instructions</span>
    <div class='section_content'>
        <div class='instructions'>$instruction</div>
        <div class='example'>
        	<img src='$exampleurl' style='width: 500px'>
    	</div>
    </div>
	<div id='submit' class='start_btn' data-user='$user->userid' data-ref='$ref->file_id'>Start</div>
</div>
";

echo json_encode($result);

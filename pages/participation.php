<?php

@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

# Classes
$user = new participant($_SESSION['userid'],$_SESSION['refid']);
$ref = new DrawRef($user->refid);
// Get previous settings
$content = $ref->get_content('consent');
$consent = $content[$user->language];

$result = "
<div id='content'>
    <span id='pagename'>Consent Form</span>
    <div class='section_content'>
        $consent
    </div>
    <div class='action_btn'>
        <div class='part_btn agree' id='agree'>I agree</div>
        <div class='part_btn decline' id='decline'>I decline</div>
    </div>
</div>
";

echo json_encode($result);

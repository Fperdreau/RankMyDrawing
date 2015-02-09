<?php

@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');
require_once($_SESSION['path_to_app'].'admin/includes/includes.php');
require_once($_SESSION['path_to_app'].'admin/conf/config.php');
check_login();

$db_set = new DB_set();
$refdrawlist = $db_set -> getinfo($ref_drawings_table,"file_id");
$options = "
    <label for='select_ref'>Select a reference</label>
    <select name='select_ref' class='select_ref'>
        <option value='all' selected>All</option>
";

foreach ($refdrawlist as $ref_id) {
    $options .= "<option value='$ref_id'>$ref_id</option>";
}
$options .= "</select>";

// Get reference drawings settings
if (!empty($refdrawlist)) {
    $content = showrefsettings();
} else {
    $content = "<p id='warning'>You must upload your reference drawings first<br>Go to the <a href='index.php?page=management'>Drawing Management section</a></p>";
}

$result = "
    <div id='content'>
        <span id='pagename'>Experiment settings</span>
        $options
        <div class='all_ref_params'>$content</div>
    </div>";
echo json_encode($result);

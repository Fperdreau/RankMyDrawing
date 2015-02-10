<?php
@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');

check_login();
$result = "
    <div id='content'>
        <span id='pagename'>Home</span>
        <p>Welcome to the RankMyDrawings Administration Interface.</p>
    </div>
";

echo json_encode($result);
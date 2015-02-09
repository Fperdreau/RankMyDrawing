<?php

// Page Header
if (!isset($_SESSION['logok']) || !$_SESSION['logok']) {
    $showlogin = "
        <span style='font-size: 16px; color: #FFFFFF;'>
        <a rel='leanModal' id='modal_trigger_login' href='#modal' class='modal_trigger'>Log in</a>";
} else {
    $showlogin = "<span style='font-size: 16px;' id='logout'><a href=''>Log out</a></span>";
}

if (!empty($_GET['page']) && $_GET['page'] == "install") {
    $sitetitle = "RankMyDrawings";
} else {
    if (!isset($db_set)) {
        $db_set = new DB_set();
    }
    $sitetitle = strtoupper($db_set->getinfo($config_table,'value',array("variable"),array("'sitetitle'")));
}

echo "
<div class='displaymenu_btn' id='on'>.:: Menu ::.</div>
<div class='header_container'>
    <div id='title'>
        <span id='sitetitle'>$sitetitle</span>
        <div style='float: right; margin-right: 10px; margin-top: 20px; height: 20px;' id='welcome'>$showlogin</div>
    </div>
</div>
";

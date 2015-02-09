<?php

// Page Header
$contact = "
    <span style='font-size: 16px; color: #FFFFFF;'>
    <a rel='leanModal' id='modal_trigger_contact' href='#modal' class='modal_trigger'>Contact</a>";

if (!empty($_GET['page']) && $_GET['page'] == "install") {
    $sitetitle = "RankMyDrawings";
} else {
    if (!isset($db_set)) {
        $db_set = new DB_set();
    }
    $sitetitle = strtoupper($db_set->getinfo($config_table,'value',array("variable"),array("'sitetitle'")));
}

echo "
<div id='progressbar'>Progression: 0%<div class='fillprogress'></div></div>
<div class='header_container'>
    <div id='title'>
        <span id='sitetitle'>$sitetitle</span>
        <div style='float: right; margin-right: 10px; margin-top: 20px; height: 20px;' id='welcome'>$contact</div>
    </div>
</div>
";

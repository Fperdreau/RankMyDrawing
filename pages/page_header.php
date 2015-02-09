<?php

// Page Header
$contact = "
    <span style='font-size: 16px; color: #FFFFFF;'>
    <a rel='leanModal' id='modal_trigger_contact' href='#modal' class='modal_trigger'>Contact</a>";

$config = new site_config('get');

echo "
<div id='progressbar'>Progression: 0%</div>
<div class='header_container'>
    <div id='title'>
        <span id='sitetitle'>$sitetitle</span>
        <div style='float: right; margin-right: 10px; margin-top: 20px; height: 20px;' id='welcome'>$contact</div>
    </div>
</div>
";

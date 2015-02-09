<?php

session_start();
session_unset();
session_destroy();

$content = "
<div id='content'>
	<span id='pagename'>Congratulations</span>
	<div class='section_content' style='text-align: center'>
		<p>Thank you for your participation</p>
		<div id='countdown'></div>
	</div>
</div>
";

echo json_encode($content);

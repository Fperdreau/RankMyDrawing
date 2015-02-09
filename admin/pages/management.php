<?php

@session_start();
require_once($_SESSION['path_to_includes'].'includes.php');
require_once($_SESSION['path_to_app'].'admin/includes/includes.php');
check_login();

// Get reference drawing list
$refdraw = new DrawRef();
$refdrawlist = $refdraw->get_refdrawinglist("file_id");
$nrefdraw = count($refdrawlist);
$imglist = "";

if ($nrefdraw>0) {

	foreach ($refdrawlist as $cur_ref) {
		$ref = new DrawRef($cur_ref);

        $itemlist = $ref->displayitems();

		$imgurl = "../images/$ref->file_id/thumb/thumb_$ref->filename";
		$upload_form = "
	        <form method='post' action='js/mini-upload-form/upload.php' enctype='multipart/form-data' id='upload' class='upl_newitems_$ref->file_id'>
		        <div id='drop'>
		            <a>Add files</a><input type='file' name='item,$ref->file_id' multiple/> Or drag it here
		        </div>
		        <ul></ul>
			</form>";

        $sort_option =  "
                <label for='order'>Sort by</label>
	            <select name='order' class='sortitems' data-ref='$ref->file_id'>
	            	<option value='' selected></option>
	            	<option value='score'>Score</option>
	            	<option value='file_id'>File ID</option>
	            	<option value='nb_occ'>Number of users</option>
	        	</select>";

	    $imglist .= "
	    <div class='refdraw-div' id='$ref->file_id'>

	        <div style='width: 100%; margin: auto;'>
                <div class='refdraw-name'>$ref->file_id</div>
                <div class='refdraw-delbutton'>
                <input type='submit' value='Delete' id='submit' data-ref='$ref->file_id' class='deleteref'/>
                </div>
	        </div>

	        <div class='refdraw-content'>
                <div class='refdraw-desc'>
                    <div class='refdraw-thumb'>
                        <img src='$imgurl' class='ref-thumb'>
                    </div>

                    <div class='refdraw-info'>
                        <p>Number of drawings: $ref->nb_draw</p>
                        <p>Number of users: $ref->nb_users</p>
                    </div>

                    <div class='upload_form'>
                        $upload_form
                    </div>

                </div>

                <div class='refdraw-half'>
                    $sort_option
                   <div class='refdraw-items'>
                    $itemlist
                    </div>
                </div>
            </div>

	    </div>";
	}
}

$result = "
	<div id='content'>
		<span id='pagename'>Drawing Management</span>
        <div class='refdraw-add' style='padding: 10px; background-color: #dddddd; margin: 10px auto 10px auto; height: 150px; width: 90%;'>
        	<div style='display: table-cell; width: 30%;'>
                <div style='display: block;'>
	        	  <label for='newref' class='label' style='width: auto;'>1. Choose a reference name</label>
                </div>
                <div style='display: block;'>
                    <form action='' id='newrefid'>
    	        		<input type='text' value='' name='newref' id='newref' />
    	        		<input type='submit' id='submit' class='newrefid' />
    	        	</form>
                </div>
        	</div>
            <div class='refupload' style='display: none; text-align: center; width: 50%; margin-left: 100px;'>
                <div style='display: block;'>
                    <label class='label' style='width: auto;'>2. Upload the reference drawing</label>
                </div>
                <div style='display: block; margin-left: 20%;'>
                    <div id='upref''></div>
                </div>
            </div>
            <div class='feedback'></div>
        </div>

        $imglist
    </div>
";

echo json_encode($result);

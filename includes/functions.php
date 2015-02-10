<?php
/*
Copyright © 2014, F. Perdreau, Radboud University Nijmegen
=======
This file is part of RankMyDrawings.

RankMyDrawings is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

RankMyDrawings is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.

*/

function check_login() {
	$cond = !isset($_SESSION['logok']) || $_SESSION['logok'] == false;

    if ($cond) {
        $result = "
		    <div id='content'>
        		<p id='warning'>You must <a rel='leanModal' id='modal_trigger_login' href='#modal' class='modal_trigger'>sign in</a> to access the different options of this interface</p>
		        </p>
		    </div>
		    ";
		echo json_encode($result);
        exit;
    }
}

function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            if (session_status() == PHP_SESSION_ACTIVE) {
                return true;
            } else {
                return false;
            }
        } else {
            if (session_id() == '') {
                return false;
            } else {
                return true;
            }
        }
    }
    return FALSE;
}

// Recursively browse files in a specified folders and subfolders
function browse($dir, $dirsNotToSaveArray = array()) {
    $filenames = array();
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            $filename = $dir."/".$file;
            if ($file != "." && $file != ".." && is_file($filename)) {
                $filenames[] = $filename;
            }

            else if ($file != "." && $file != ".." && is_dir($dir.$file) && !in_array($dir.$file, $dirsNotToSaveArray) ) {
                $newfiles = browse($dir.$file,$dirsNotToSaveArray);
                $filenames = array_merge($filenames,$newfiles);
            }
        }
        closedir($handle);
    }
    return $filenames;
}

// Export target db to xls file
function exportdbtoxls($tablename) {
    /***** EDIT BELOW LINES *****/
    $db_set = new DB_set();
    $DB_Server = $db_set->host; // MySQL Server
    $DB_Username = $db_set->username; // MySQL Username
    $DB_Password = $db_set->password; // MySQL Password
    $DB_DBName = $db_set->dbname; // MySQL Database Name
    $DB_TBLName = $tablename; // MySQL Table Name
    $xls_filename = 'backup/export_'.$tablename.date('Y-m-d').'.xls'; // Define Excel (.xls) file name
	$out = "";
    /***** DO NOT EDIT BELOW LINES *****/
    // Create MySQL connection
    $sql = "Select * from $DB_TBLName";
    $Connect = @mysql_connect($DB_Server, $DB_Username, $DB_Password) or die("Failed to connect to MySQL:<br />" . mysql_error() . "<br />" . mysql_errno());
    // Select database
    $Db = @mysql_select_db($DB_DBName, $Connect) or die("Failed to select database:<br />" . mysql_error(). "<br />" . mysql_errno());
    // Execute query
    $result = @mysql_query($sql,$Connect) or die("Failed to execute query:<br />" . mysql_error(). "<br />" . mysql_errno());

    // Header info settings
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=$xls_filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    /***** Start of Formatting for Excel *****/
    // Define separator (defines columns in excel &amp; tabs in word)
    $sep = "\t"; // tabbed character

    // Start of printing column names as names of MySQL fields
    for ($i = 0; $i<mysql_num_fields($result); $i++) {
        $out .= mysql_field_name($result, $i) . "\t";
    }
    $out .= "\n";
    // End of printing column names

    // Start while loop to get data
    while($row = mysql_fetch_row($result))
    {
        $schema_insert = "";
        for($j=0; $j<mysql_num_fields($result); $j++)
        {
            if(!isset($row[$j])) {
                $schema_insert .= "NULL".$sep;
            }
            elseif ($row[$j] != "") {
                $schema_insert .= "$row[$j]".$sep;
            }
            else {
                $schema_insert .= "".$sep;
            }
        }
        $schema_insert = str_replace($sep."$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        $out .= trim($schema_insert);
        $out .=  "\n";
    }

	if ($fp = fopen($_SESSION['path_to_app'].$xls_filename, "w+")) {
        if (fwrite($fp, $out) == true) {
            fclose($fp);
        } else {
            $result = "Impossible to write";
	        echo json_encode($result);
			exit;
        }
    } else {
        $result = "Impossible to open the file";
        echo json_encode($result);
        exit;
    }
    chmod($_SESSION['path_to_app'].$xls_filename,0644);

    return $xls_filename;
}

// Backup routine
function backup_db(){
    require_once($_SESSION['path_to_includes'].'db_connect.php');
    require_once($_SESSION['path_to_includes'].'site_config.php');
    require_once($_SESSION['path_to_includes'].'users.php');

    // Declare classes
    $db_set = new DB_set();
    $config = new site_config('get');

    // Create Backup Folder
    $mysqlSaveDir = $_SESSION['path_to_app'].'backup/Mysql';
    $fileNamePrefix = 'fullbackup_'.date('Y-m-d_H-i-s');

    if (!is_dir($mysqlSaveDir)) {
        mkdir($mysqlSaveDir,0777);
    }

    // Do backup
    /* Store All Table name in an Array */
    $allTables = array();
    $result = $db_set->send_query('SHOW TABLES');
    while($row = mysqli_fetch_row($result)){
        $allTables[] = $row[0];
    }

    $return = "";
    foreach($allTables as $table){
        $result = $db_set->send_query('SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row($db_set->send_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while($row = mysqli_fetch_row($result)){
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++){
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; }
                    else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n";
    }
    $handle = fopen($mysqlSaveDir."/".$fileNamePrefix.".sql",'w+');
    fwrite($handle,$return);
    fclose($handle);

    // Check for previous backup and delete old ones
    $oldbackup = browse($mysqlSaveDir);
    $cpt = 0;
    foreach ($oldbackup as $old) {
        $prop = explode('_',$old);
        $back_date = $prop[1];
        $today = date('Y-m-d');
        $lim_date = date("Y-m-d",strtotime($today." - $config->clean_day days"));
        // Delete file if too old
        if ($back_date <= $lim_date) {
            if (is_file($old)) {
                $cpt++;
                unlink($old);
            }
        }
    }
    return "http://".$config->site_url."/backup/Mysql/$fileNamePrefix.sql";
}

// Mail backup file to admins
function mail_backup($backupfile) {
    require_once($_SESSION['path_to_includes'].'myMail.php');
    $mail = new myMail();
    $admin = new users();
    $admin->getuserinfo('admin');

    // Send backup via email
    $content = "
    Hello, <br>
    <p>This message has been sent automatically by the server. You may find a backup of your database in attachment.</p>
    ";
    $body = $mail -> formatmail($content);
    $subject = "Automatic Database backup";
    if ($mail->send_mail($admin->email,$subject,$body,$backupfile)) {
    }
}

// Full backup routine
function file_backup() {

    $dirToSave = $_SESSION['path_to_app'];
    $dirsNotToSaveArray = array($_SESSION['path_to_app']."backup");
    $mysqlSaveDir = $_SESSION['path_to_app'].'backup/Mysql';
    $zipSaveDir = $_SESSION['path_to_app'].'backup/Complete';
    $fileNamePrefix = 'fullbackup_'.date('Y-m-d_H-i-s');

    if (!is_dir($zipSaveDir)) {
        mkdir($zipSaveDir,0777);
    }

    system("gzip ".$mysqlSaveDir."/".$fileNamePrefix.".sql");
    system("rm ".$mysqlSaveDir."/".$fileNamePrefix.".sql");

    $zipfile = $zipSaveDir.'/'.$fileNamePrefix.'.zip';

    // Check if backup does not already exist
    $filenames = browse($dirToSave,$dirsNotToSaveArray);

    $zip = new ZipArchive();

    if ($zip->open($zipfile, ZIPARCHIVE::CREATE)!==TRUE) {
        return "cannot open <$zipfile>";
    } else {
        foreach ($filenames as $filename) {
            $zip->addFile($filename,$filename);
        }

        $zip->close();
        return "backup/complete/$fileNamePrefix.zip";
    }
}

// Image functions
function imagethumb( $image_src , $image_dest = NULL , $max_size = 100, $expand = FALSE, $square = FALSE ) 	{
    if( !file_exists($image_src) ) return FALSE;

    // Récupère les infos de l'image
    $fileinfo = getimagesize($image_src);
    if( !$fileinfo ) return FALSE;

    $width     = $fileinfo[0];
    $height    = $fileinfo[1];
    $type_mime = $fileinfo['mime'];
    $type      = str_replace('image/', '', $type_mime);

    if( !$expand && max($width, $height)<=$max_size && (!$square || ($square && $width==$height) ) ) {
        // L'image est plus petite que max_size
        if($image_dest)	{
            return copy($image_src, $image_dest);
        } else {
            header('Content-Type: '. $type_mime);
            return (boolean) readfile($image_src);
        }
    }

    // Calcule les nouvelles dimensions
    $ratio = $width / $height;
    if( $square )	{
        $new_width = $new_height = $max_size;
        if( $ratio > 1 ) {
            // Paysage
            $src_y = 0;
            $src_x = round( ($width - $height) / 2 );

            $src_w = $src_h = $height;
        } else {
            // Portrait
            $src_x = 0;
            $src_y = round( ($height - $width) / 2 );

            $src_w = $src_h = $width;
        }
    } else {
        $src_x = $src_y = 0;
        $src_w = $width;
        $src_h = $height;

        if ( $ratio > 1 ) {
            // Paysage
            $new_width  = $max_size;
            $new_height = round( $max_size / $ratio );
        } else {
            // Portrait
            $new_height = $max_size;
            $new_width  = round( $max_size * $ratio );
        }
    }

    // Ouvre l'image originale
    $func = 'imagecreatefrom' . $type;
    if( !function_exists($func) ) return FALSE;

    $image_src = $func($image_src);
    $new_image = imagecreatetruecolor($new_width,$new_height);

    // Gestion de la transparence pour les png
    if( $type=='png' )	{
        imagealphablending($new_image,false);
        if( function_exists('imagesavealpha') )
            imagesavealpha($new_image,true);

        // Gestion de la transparence pour les gif
    } elseif( $type=='gif' && imagecolortransparent($image_src)>=0 ) {
        $transparent_index = imagecolortransparent($image_src);
        $transparent_color = imagecolorsforindex($image_src, $transparent_index);
        $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($new_image, 0, 0, $transparent_index);
        imagecolortransparent($new_image, $transparent_index);
    }

    // Redimensionnement de l'image
    imagecopyresampled(
        $new_image, $image_src,
        0, 0, $src_x, $src_y,
        $new_width, $new_height, $src_w, $src_h
    );

    // Enregistrement de l'image
    $func = 'image'. $type;
    if($image_dest)	{
        $func($new_image, $image_dest);
    }

    // Libération de la mémoire
    imagedestroy($new_image);
    return true;
}

// Upload image
function upload_img($tmp,$directory,$filename) {
    $dest = $directory.$filename;
    if (!is_dir($directory)) {
        mkdir($directory);
    }
    chmod($directory,0777);
    $result = move_uploaded_file($tmp,$dest);
    chmod($directory,0655);
    return $result;
}

function upload_thumb($filename,$src_dir,$dest_dir,$size = 100) {
    $thumb_name = 'thumb_'. $filename;
    $dest = $dest_dir.$thumb_name;
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir);
    }
    chmod($dest_dir,0777);

    // Make thumb
    $result =  imagethumb( $src_dir.$filename , $dest , $size);
    chmod($dest_dir,0655);
    return $result;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

// Compute a factorial number
function factorial($number) {
    if ($number < 2) {
        return 1;
    } else {
        return ($number * factorial($number-1));
    }
}

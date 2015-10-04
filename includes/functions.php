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

require_once('boot.php');

function check_login() {
	$cond = !isset($_SESSION['logok']) || $_SESSION['logok'] == false;

    if ($cond) {
        $result = "
		    <div id='content'>
        		<p id='warning'>You must <span class='leanModal' id='user_login' data-section='user_login'>
        		log in</span> to access the different options of this interface</p>
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
function exportDb($tablename) {
    global $db;
    $out = "";
    $xls_filename = $tablename."_".date('Y-m-d').'.xls'; // Define Excel (.xls) file name

    $sql = "Select * from $tablename";
    $result = $db->send_query($sql);

    // Header info settings
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=$xls_filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    /***** Start of Formatting for Excel *****/
    // Define separator (defines columns in excel &amp; tabs in word)
    $sep = "\t"; // tabbed character

    $columns = $db->getcolumns($tablename);
    foreach ($columns as $column) {
    // Start of printing column names as names of MySQL fields
        $out .= $column . "\t";
    }
    $out .= "\n";
    // End of printing column names

    // Start while loop to get data
    while($row = mysqli_fetch_assoc($result)) {
        $schema_insert = "";
        foreach ($columns as $column) {
            if(empty($row[$column])) {
                $schema_insert .= "NULL".$sep;
            }
            elseif ($row[$column] != "") {
                $schema_insert .= "$row[$column]".$sep;
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

    if ($fp = fopen(PATH_TO_APP.'/backup/'.$xls_filename, "w+")) {
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
    chmod(PATH_TO_APP.'/backup/'.$xls_filename,0644);
    return PATH_TO_APP."/backup/$xls_filename";
}

/**
 * exportdbtoxls()
 * Export reference drawing's tables to xls file and return an archive
 * @param $refname
 * @param $tableList
 * @return string
 */
function exportdbtoxls($refname, $tableList) {
    global $AppConfig;

    $zipSaveDir = PATH_TO_APP.'/backup/Complete';
    $fileNamePrefix = $refname."_".date('Y-m-d_H-i-s');
    $filenames = array();
    foreach ($tableList as $tablename) {
        $filenames[] = exportDb($tablename);
    }
    $zipfile = $zipSaveDir.'/'.$fileNamePrefix.'.zip';

    $zip = new ZipArchive();

    if ($zip->open($zipfile, ZIPARCHIVE::CREATE)!==TRUE) {
        return "cannot open <$zipfile>";
    } else {
        foreach ($filenames as $filename) {
            $zip->addFile($filename);
        }

        $zip->close();
        return $AppConfig->site_url."backup/Complete/$fileNamePrefix.zip";
    }
}

// Backup routine
function backup_db(){
    global $db, $AppConfig;

    // Create Backup Folder
    $mysqlSaveDir = PATH_TO_APP.'/backup/Mysql';
    $fileNamePrefix = 'fullbackup_'.date('Y-m-d_H-i-s');

    if (!is_dir($mysqlSaveDir)) {
        mkdir($mysqlSaveDir,0777);
    }

    // Do backup
    /* Store All AppTable name in an Array */
    $allTables = array();
    $result = $db->send_query('SHOW TABLES');
    while($row = mysqli_fetch_row($result)){
        $allTables[] = $row[0];
    }

    $return = "";
    foreach($allTables as $table){
        $result = $db->send_query('SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row($db->send_query('SHOW CREATE TABLE '.$table));
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

    cleanbackups($mysqlSaveDir);
    return $AppConfig->site_url."/backup/Mysql/$fileNamePrefix.sql";
}

// Mail backup file to admins
function mail_backup($backupfile) {
    global $AppMail, $db;
    $admin = new Users($db);
    $admin->get('admin');

    // Send backup via email
    $content = "
    Hello, <br>
    <p>This message has been sent automatically by the server. You may find a backup of your database in attachment.</p>
    ";
    $body = $AppMail -> formatmail($content);
    $subject = "Automatic Database backup";
    if ($AppMail->send_mail($admin->email,$subject,$body,$backupfile)) {
    }
}


/**
 * Check for previous backup and delete the oldest ones
 * @param $mysqlSaveDir
 */
function cleanbackups($mysqlSaveDir) {
    $db = new AppDb();
    $config = new AppConfig($db);
    $oldbackup = browse($mysqlSaveDir);
    if (!empty($oldbackup)) {
        $files = array();
        // First get files date
        foreach ($oldbackup as $file) {
            $filewoext = explode('.',$file);
            $filewoext = $filewoext[0];
            $prop = explode('_',$filewoext);
            if (count($prop)>1) {
                $back_date = $prop[1];
                $back_time = $prop[2];
                $formatedtime = str_replace('-',':',$back_time);
                $date = $back_date." ".$formatedtime;
                $files[$date] = $file;
            }
        }

        // Sort backup files by date
        krsort($files);

        // Delete oldest files
        $cpt = 0;
        foreach ($files as $date=>$old) {
            // Delete file if too old
            if ($cpt >= $config->clean_day) {
                if (is_file($old)) {
                    unlink($old);
                }
            }
            $cpt++;
        }
    }
}

// Full backup routine
function file_backup() {
    global  $AppConfig;
    $dirToSave = PATH_TO_APP;
    $dirsNotToSaveArray = array(PATH_TO_APP."/backup");
    $mysqlSaveDir = PATH_TO_APP.'/backup/Mysql';
    $zipSaveDir = PATH_TO_APP.'/backup/Complete';
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

    if ($zip->open($zipfile, ZipArchive::CREATE)!==true) {
        return "cannot open <$zipfile>";
    } else {
        foreach ($filenames as $filename) {
            $zip->addFile($filename);
        }
        $zip->close();
        return $AppConfig->site_url."backup/Complete/$fileNamePrefix.zip";
    }
}

/**
 * Create image thumbs
 * @param $image_src: source file
 * @param null $image_dest: destination folder
 * @param int $max_size: maximum image resolution
 * @param bool|FALSE $expand
 * @param bool|FALSE $square
 * @return bool
 */
function imagethumb( $image_src , $image_dest = NULL , $max_size = 100, $expand = FALSE, $square = FALSE ) 	{
    if( !file_exists($image_src) ) return FALSE;

    // Get image info
    $fileinfo = getimagesize($image_src);
    if( !$fileinfo ) return FALSE;

    $width     = $fileinfo[0];
    $height    = $fileinfo[1];
    $type_mime = $fileinfo['mime'];
    $type      = str_replace('image/', '', $type_mime);

    if( !$expand && max($width, $height)<=$max_size && (!$square || ($square && $width==$height) ) ) {
        // image is smaller than max size
        if($image_dest)	{
            return copy($image_src, $image_dest);
        } else {
            header('Content-Type: '. $type_mime);
            return (boolean) readfile($image_src);
        }
    }

    // Compute new dimensions
    $ratio = $width / $height;
    if( $square )	{
        $new_width = $new_height = $max_size;
        if( $ratio > 1 ) {
            // Landscape
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
            // Landscape
            $new_width  = $max_size;
            $new_height = round( $max_size / $ratio );
        } else {
            // Portrait
            $new_height = $max_size;
            $new_width  = round( $max_size * $ratio );
        }
    }

    // Create new image from the original
    $func = 'imagecreatefrom' . $type;
    if( !function_exists($func) ) return FALSE;

    $image_src = $func($image_src);
    $new_image = imagecreatetruecolor($new_width,$new_height);

    // Transparency for PNG
    if( $type=='png' )	{
        imagealphablending($new_image,false);
        if( function_exists('imagesavealpha') )
            imagesavealpha($new_image,true);

        // Transparency for GIF
    } elseif( $type=='gif' && imagecolortransparent($image_src)>=0 ) {
        $transparent_index = imagecolortransparent($image_src);
        $transparent_color = imagecolorsforindex($image_src, $transparent_index);
        $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($new_image, 0, 0, $transparent_index);
        imagecolortransparent($new_image, $transparent_index);
    }

    // Resize image
    imagecopyresampled(
        $new_image, $image_src,
        0, 0, $src_x, $src_y,
        $new_width, $new_height, $src_w, $src_h
    );

    // Save image
    $func = 'image'. $type;
    if($image_dest)	{
        $func($new_image, $image_dest);
    }

    // Free memory
    imagedestroy($new_image);
    return true;
}

/**
 * Create a thumb image
 * @param $filename: source file name
 * @param $src_dir: path to source file
 * @param $dest_dir: destination folder
 * @param int $size: maximal width of the thumb version
 * @return bool
 */
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

/**
 * Delete reference drawing folder
 * @param $dir
 * @return bool
 */
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

/**
 * Compute a factorial number
 * @param $number
 * @return int
 */
function factorial($number) {
    if ($number < 2) {
        return 1;
    } else {
        return ($number * factorial($number-1));
    }
}

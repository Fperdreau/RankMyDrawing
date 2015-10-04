<?php
/**
 * File for class Uploads and class Media
 *
 * PHP version 5
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of Journal Club Manager.
 *
 * Journal Club Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Journal Club Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */

class Uploads extends AppTable {

    protected $table_data = array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "fileid" => array("CHAR(50)", false),
        "filename" => array("CHAR(50)", false),
        "date" => array("DATETIME NOT NULL"),
        "type" => array("CHAR(50)", false),
        "directory" => array("CHAR(255)", false),
        "primary" => "id"
    );
    protected $directory=PATH_TO_IMG;
    protected $maxsize=2000*1000;
    protected $allowed_types=array('png');
    protected $max_resolution=array(4000,4000);
    public $fileid;
    public $date;
    public $filename;
    public $type;

    /**
     * Constructor
     * @param AppDb $db
     */
    function __construct(AppDb $db, $fileid=null) {
        parent::__construct($db, "Uploads", $this->table_data);

        // Create uploads folder if it does not exist yet
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }

        if (!is_null($fileid)) {
            $this->get($fileid);
        }
    }

    /**
     * Create Media object
     * @param $file : $_FILES
     * @param null $directory : Destination folder
     * @param null $filename: file name
     * @return bool|mixed|mysqli_result|string
     */
    public function make($file, $directory=null, $filename=null) {

        // Create uploads folder if it does not exist yet
        $this->directory = ($directory !== null) ? $directory:$this->directory;
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }

        // First check the file
        $result['error'] = $this->checkupload($file);
        if ($result['error'] != true) {
            return $result;
        }

        // Second: Proceed to upload
        $result = $this->upload($file,$filename);
        if ($result['error'] !== true) {
            return $result;
        }

        $this->date = date('Y-m-d h:i:s');

        // Third: add to the Media table
        $class_vars = get_class_vars('Uploads');
        $content = $this->parsenewdata($class_vars,array(),array('max_resolution','maxsize','allowed_types'));
        $result['error'] = $this->db->addcontent($this->tablename,$content);
        if ($result['error'] !== true) {
            $result['error'] = 'SQL: Could not add the file to the media table';
        }

        return $result;
    }

    /**
     * @param $fileid
     * @return bool
     */
    function get($fileid) {
        $sql = "SELECT * FROM $this->tablename WHERE fileid='$fileid'";
        $req = $this->db->send_query($sql);
        $data = mysqli_fetch_assoc($req);
        if (!empty($data)) {
            foreach ($data as $key=>$value) {
                $this->$key = htmlspecialchars_decode($value);
            }
        } else {
            return false;
        }
        $this->checkfiles();
        return true;
    }

    /**
     * Check consistency between the media table and the files actually stored on the server
     * @return bool
     */
    function checkfiles () {
        // First check if the db points to an existing file
        if (!is_file($this->directory.$this->filename)) {
            // If not, we remove the data from the db
            return $this->delete();
        } else {
            return true;
        }
    }

    /**
     * Delete a file
     * @return bool|string
     */
    function delete() {
        // Check if file exists
        if (is_file($this->directory.$this->filename)) {
            // If it exists, try to delete it
            if (unlink($this->directory.$this->filename)) {
                // If deleted, remove its corresponding entry in DB
                if ($this->db->deletecontent($this->tablename,'fileid',$this->fileid)) {
                    $result['status'] = true;
                    $result['msg'] = "File Deleted";
                } else {
                    $result['status'] = false;
                    $result['msg'] = "Could not remove entry from DB";
                }
            } else {
                $result['status'] = false;
                $result['msg'] = "Could not delete the file";
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "File does not exist";
        }
        return $result;
    }

    /**
     * Validate upload
     * @param $file
     * @return bool|string
     */
    private function checkupload($file) {
        if ($file['error'][0] != 0) {
            switch ($file['error'][0]) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    return "No file to upload";
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return 'File Exceeds size limit';
                default:
                    return "Unknown error";
            }
        }

        // You should also check file size here.
        if ($file['size'][0] > $this->maxsize) {
            return "File Exceeds size limit";
        }

        // Check extension
        $filename = basename($file['name'][0]);
        $ext = substr($filename, strrpos($filename, '.') + 1);

        if (false === in_array($ext,$this->allowed_types)) {
            return "Invalid file type";
        } else {
            list($width, $height, $type, $attr) = getimagesize($file['tmp_name'][0]);
            if ($width > $this->max_resolution[0] || $height > $this->max_resolution[1]) {
                return "Wrong resolution (max: ".$this->max_resolution[0]."x".$this->max_resolution[1].")";
            }
            return true;
        }
    }

    /**
     * Upload a file
     * @param $file
     * @param null $filename: file ID
     * @return mixed
     */
    public function upload($file, $filename=null) {
        $result['status'] = false;
        if (isset($file['tmp_name'][0]) && !empty($file['name'][0])) {
            $result['error'] = self::checkupload($file);
            if ($result['error'] === true) {
                $tmp = htmlspecialchars($file['tmp_name'][0]);
                $splitname = explode(".", strtolower($file['name'][0]));
                $this->type = end($splitname);

                // Create a unique filename
                if (!is_null($filename)) {
                    $this->fileid = $filename;
                    $this->filename = $filename.'.'.$this->type;
                } else {
                    $this->filename = $this->makeId();
                }

                // Move file to the upload folder
                $destination = $this->directory.$this->filename;
                $results['error'] = move_uploaded_file($tmp,$destination);

                if ($results['error'] == false) {
                    $result['error'] = "Uploading process failed";
                } else {
                    $results['error'] = true;
                    $result['status'] = $this->fileid;
                }
            }
        } else {
            $result['error'] = "No File to upload";
        }
        return $result;
    }

    public function makeId() {
        $rnd = date('Ymd')."_".rand(0,100);
        $newname = $rnd.".".$this->type;
        while (is_file($this->directory.$newname)) {
            $rnd = date('Ymd')."_".rand(0,100);
            $newname = $rnd.".".$this->type;
        }
        $this->fileid = $rnd;
        $this->filename = $newname;
        return $this->filename;
    }
}
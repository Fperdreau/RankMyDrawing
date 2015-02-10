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

require_once($_SESSION['path_to_app'].'includes/db_connect.php');

class DrawRef {

    public $file_id = "";
    public $filename = "";
    public $date = "";
    public $nb_users = 0;
    public $max_nb_users = 200;
    public $nb_draw = 0;
    public $max_nb_pairs = 0;
    public $initial_score = 1500;
    public $nb_pairs = 0;
    public $status = "on";
    public $filter = "off";

    // Constructor
    function __construct($file_id = null) {
        if (null != $file_id) {
            self::get($file_id);
        }
    }

    // Make object.
    public function make($file_id,$file) {
        require($_SESSION['path_to_app'].'admin/conf/config.php');
        $db_set = new DB_set();
        $this->file_id = $file_id;

        // Make folders
        $result = self::make_folders();
        if ($result == false) {
            return $result;
        }

        $this->filename = self::upload($file);
        $this->date = date('Y-m-d H:i:s');
        $this->drawlist = self::get_refdrawinglist("filename");
        $this->nb_draw = count($this->drawlist);

        $class_vars = get_class_vars("DrawRef");
        $variables = implode(',',array_keys($class_vars));
        $values = array();
        foreach ($class_vars as $name=>$value) {
            $extvalue = mysqli_real_escape_string($db_set->bdd,$this->$name);
            $values[]= "'$extvalue'";
        }
        $values = implode(',',$values);

        // Add an entry in the ref_drawings table
        $db_set -> addcontent($ref_drawings_table,$variables,$values);

        // Create corresponding tables
        self::create_reftable();
        self::get($this->file_id);
        return $this->filename;
	}

	// Create images folders
    private function make_folders() {
        $ref_directory = $_SESSION['path_to_app']."images/$this->file_id/";
        $img_directory = $_SESSION['path_to_app']."images/$this->file_id/img/";
        $thumb_directory = $_SESSION['path_to_app']."images/$this->file_id/thumb/";
        if (!is_dir($ref_directory)) {
            if (!mkdir($ref_directory)) {
                echo json_encode("Failed to create $ref_directory");
                return false;
            }
        }
        chmod($ref_directory,0755);

        if (!is_dir($img_directory)) {
            if (!mkdir($img_directory)) {
                echo json_encode("Failed to create $img_directory");
                return false;
            }
        }
        chmod($img_directory,0755);

        if (!is_dir($thumb_directory)) {
            if (!mkdir($thumb_directory)) {
                echo json_encode("Failed to create $thumb_directory");
                return false;
            }
        }
        chmod($thumb_directory,0755);
        return true;
    }

    // Update
    public function update($post,$file_id=null) {
        require($_SESSION['path_to_app']."admin/conf/config.php");
        $db_set = new DB_set();

        if (null!=$file_id) {
            $this->$file_id = $file_id;
        } elseif (array_key_exists('id',$post)) {
            $this->$file_id = $_POST['id'];
        }

        $class_vars = get_class_vars("DrawRef");
        foreach ($post as $name => $value) {
            $value = htmlspecialchars($value);
            if (array_key_exists($name,$class_vars)) {
                $db_set->updatecontent($ref_drawings_table,$name,"'$value'",array("file_id"),array("'$this->file_id'"));
                $this->$name = $value;
            }
        }
        return "updated";
    }

    // Get information
    public function get($file_id) {
        require($_SESSION['path_to_app'].'admin/conf/config.php');
        $db_set = new DB_set();

        if (self::exists($file_id)) {
            $sql = "SELECT * FROM $ref_drawings_table WHERE file_id='$file_id'";
            $req = $db_set->send_query($sql);
            $class_vars = get_class_vars("DrawRef");
            $row = mysqli_fetch_assoc($req);
            foreach ($row as $varname=>$value) {
                if (array_key_exists($varname,$class_vars)) {
                    $this->$varname = $value;
                }
            }
            $this->drawlist = self :: get_drawingslist();
            $this->max_nb_pairs = self :: getmaxnbpairs();
            $post['max_nb_pairs'] = $this->max_nb_pairs;
            $post['nb_users'] = self :: getnbusers();
            $post['nb_draw'] = count($this->drawlist);
            self :: update($post);
            return true;
        } else {
            return false;
        }
    }

    // Check if ref drawing exists in the database (return true if it exists)
    public function exists($file_id) {
        $reflist = self::get_refdrawinglist();
        return in_array($file_id,$reflist);
    }

	// Get number of participants
	private function getnbusers() {
        return count(self::getusers());
	}

    // Get maximum number of possible pairs
    private function getmaxnbpairs() {
        return factorial($this->nb_draw)/(factorial($this->nb_draw-2)*factorial(2));
    }

    // Get users list
    function getusers($property = "userid") {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->file_id."_users";
        $sql = "SELECT $property FROM $reftable";
        $req = $db_set->send_query($sql);
        $users = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $users[] = $row[$property];
        }
        return $users;
    }

	// Make an unique ID
	function makeID() {
        require($_SESSION['path_to_app']."admin/conf/config.php");
        $db_set = new DB_set();
        $file_id = $this->file_id."_".rand(1,10000);

        // Check if random ID does not already exist in our database
        $prev_id = $db_set->getinfo($ref_drawings_table,'file_id');
        while (in_array($file_id,$prev_id)) {
            $file_id = $this->file_id."_".rand(1,10000);
        }
        return $file_id;
	}

	// Upload reference file
	function upload($file) {

		if (isset($file['tmp_name']) && !empty($file['name'])) {
            $tmp = htmlspecialchars($file['tmp_name']);
            $splitname = explode(".", strtolower($file['name']));
            $extension = end($splitname);

			$img_directory = $_SESSION['path_to_app']."images/$this->file_id/img/";
            $thumb_directory = $_SESSION['path_to_app']."images/$this->file_id/thumb/";
            chmod($img_directory,0777);
            chmod($thumb_directory,0777);

            $newname = self::makeID().".".$extension;

            if (upload_img($tmp,$img_directory,$newname)) {
                if (upload_thumb($newname,$img_directory,$thumb_directory,100)) {
                    return $newname;
                } else {
                    return "thumb not uploaded";
                }
            } else {
                return "file not uploaded";
            }
        } else {
            $newname = "no_file";
            return $newname;
        }
	}

	// Get list of items corresponding to the current object
	function get_refdrawinglist($property="file_id") {
		require($_SESSION['path_to_app'].'admin/conf/config.php');
		$db_set = new DB_set();
        $refdrawlist = $db_set->getinfo($ref_drawings_table,$property);
		return $refdrawlist;
	}

	// Get list of items corresponding to the current object
	function get_drawingslist($filter=null) {
		require($_SESSION['path_to_app'].'admin/conf/config.php');
		$db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->file_id."_ranking";
        $sql = "SELECT file_id FROM $reftable";
        if (null != $filter) {
            $sql .= " ORDER BY $filter DESC";
        }
        $req = $db_set->send_query($sql);
        $drawlist = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $drawlist[] = $row['file_id'];
        }
        return $drawlist;
	}

    public function selectdrawref($user_ip) {
        require($_SESSION['path_to_app'].'admin/conf/config.php');
        $db_set = new DB_set();
        $ref = new DrawRef();
        $reflist = self::get_refdrawinglist();

        // Update all ref drawings tables
        foreach ($reflist as $ref_id) {
            $ref = new DrawRef($ref_id);
        }

        $sql = "SELECT file_id FROM $ref_drawings_table WHERE status='on' and nb_users<max_nb_users and nb_draw>='2' ORDER BY nb_users ASC";
        $req = $db_set->send_query($sql);
        $validref = false;
        while ($row = mysqli_fetch_assoc($req)) {
            $ref_id = $row['file_id'];
            $cur_ref = new DrawRef($ref_id);

            // Check if user already exists for this ref drawing and if this ref drawing's settings allow users to do it again
            $user_exist = $cur_ref->checkuser($user_ip);
            if ($user_exist == false || ($user_exist == true && $cur_ref->filter == "off")) {
                $validref = $cur_ref->file_id;
                return $validref;
            }
        }
        return $validref;
    }

    // Check if user exists (ip checking)
    public function checkuser($user_ip) {
        $users = self::getusers('ip');
        return in_array($user_ip, $users);
    }

    // Delete current object (database &  corresponding files)
    function delete() {
        require($_SESSION['path_to_app'].'admin/conf/config.php');
        $db_set = new DB_set();

        // Delete corresponding entry in the ref_drawings table
        $db_set -> deletecontent($ref_drawings_table,array("file_id"),array("'$this->file_id'"));

        // Delete corresponding tables
        $tablenames = self::get_tables();
        foreach ($tablenames as $table_name) {
            $db_set -> deletetable($table_name);
        }

        // Delete related files
        $img_path = $_SESSION['path_to_app']."images/$this->file_id/";
        deleteDirectory($img_path);
        return true;
    }

    // Get tables name
    public function get_tables() {
        $db_set = new DB_set();
        $table_name = $db_set->dbprefix.$this->file_id."_%";
        $sql = "SHOW TABLES FROM $db_set->dbname LIKE '$table_name'";
        $req = $db_set->send_query($sql);
        $result = array();
        while ($row = mysqli_fetch_array($req)) {
            $result [] = $row[0];
        }
        return $result;
    }

	// Create related tables in the database
	function create_reftable() {
		require($_SESSION['path_to_app'].'admin/conf/config.php');
		$db_set = new DB_set();

		// Create tables
		$table_prefix = $this->file_id;

		$tables = array(
			"table1" => $db_set->dbprefix.$table_prefix."_users",
			"table2" => $db_set->dbprefix.$table_prefix."_ranking",
			"table3" => $db_set->dbprefix.$table_prefix."_comp_mat",
			"table4" => $db_set->dbprefix.$table_prefix."_res_mat",
            "table5" => $db_set->dbprefix.$table_prefix."_content");

		$cols = array(
			"table1" => "`id` INT NOT NULL AUTO_INCREMENT,
                `ip` CHAR(50),
                `date` DATETIME,
                `userid` CHAR(20),
                `refid` CHAR(20),
                `nb_visit` INT NOT NULL,
                `name` CHAR(10),
                `email` TEXT(50),
                `language` CHAR(3),
                `age` INT(3) NOT NULL,
                `gender` CHAR(6),
                `drawlvl` CHAR(10),
                `artint` CHAR(3) NOT NULL,
                `response1` TEXT,
                `response2` TEXT,
                `pair1` TEXT,
                `pair2` TEXT,
                `time_start` INT(50) NOT NULL,
                `time_end` INT(50) NOT NULL,
                PRIMARY KEY(id)",
            "table2" => "`id` INT NOT NULL AUTO_INCREMENT,
                `file_id` CHAR(20),
                `filename` CHAR(20),
                `date` DATE,
                `nb_win` INT(4) NOT NULL,
                `nb_occ` INT(4) NOT NULL,
                `score` FLOAT NOT NULL,
                `rank` INT NOT NULL,
                PRIMARY KEY(id)",
            "table3" => "`id` INT NOT NULL AUTO_INCREMENT,
                `file_id` CHAR(20),
                PRIMARY KEY(id)",
            "table4" => "`id` INT NOT NULL AUTO_INCREMENT,
                `file_id` CHAR(20),
                PRIMARY KEY(id)",
            "table5" => "`id` INT NOT NULL AUTO_INCREMENT,
                `type` CHAR(20),
                `lang` CHAR(20),
                `content` TEXT,
                PRIMARY KEY(id)
            ");

        $ntable = count($tables);

        // Create or update tables
        for ($i=1; $i<=$ntable; $i++) {
            $table_name = $tables["table".$i];
            $cols_name = $cols['table'.$i];
            $db_set->createtable($table_name,$cols_name,1);
        }

        // Add default content
        $config = new site_config('get');
        $db_set->addcontent($tables['table5'],'type,lang,content',"'instruction','en','$config->instruction'");
        $db_set->addcontent($tables['table5'],'type,lang,content',"'consent','en','$config->consent'");
    }

    // Display items corresponding to the current object
    public function displayitems($filter=null) {
        require($_SESSION['path_to_app'].'admin/conf/config.php');
        $drawlist = self::get_drawingslist($filter);
        if (!empty($drawlist)) {
            $result = "";
            foreach ($drawlist as $id) {
                $item = new Elo($this->file_id,$id);
                $thumb_url = "../images/$item->refid/thumb/thumb_$item->filename";
                $del_url = "../images/delete.png";
                $result .= "
                    <div class='item' id='item_$item->file_id'>
                        <div style='font-size: 12px; float: left;'>Score: $item->score</div>
                        <div class='delete_btn_item' id='$item->file_id' data-item='$item->refid'>
                            <img src='$del_url' alt='Delete $item->file_id' style='width: 10px; height: 10px;'>
                        </div>
                        <div class='thumb'>
                            <a rel='item_leanModal' id='modal_trigger_showitem' href='#item_modal' class='modal_trigger' data-ref='$item->refid' data-item='$item->file_id'><img src='$thumb_url' class='thumb' alt='$item->file_id'></a>
                        </div>
                    </div>
                ";
            }
        } else {
            $result = "<span id='warning'>There are no items for this reference drawing yet</span>";
        }

        return $result;
    }

	// Get instructions and consent forms related to the current object
    public function get_content($type) {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->file_id.'_content';
        $sql = "SELECT lang,content FROM $reftable WHERE type='$type'";
        $req = $db_set->send_query($sql);
        $content = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $content[$row['lang']] = htmlspecialchars_decode($row['content']);
        }
        return $content;
    }

    // Get content languages
    public function getlanguages() {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->file_id.'_content';
        $sql = "SELECT lang FROM $reftable WHERE type='instruction'";
        $req = $db_set->send_query($sql);
        $languages = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $languages[] = $row['lang'];
        }
        return $languages;
    }

}

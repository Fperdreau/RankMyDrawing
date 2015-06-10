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


class DrawRef extends AppTable {

    protected $table_data = array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "file_id" => array("CHAR(50)", false),
        "filename" => array("CHAR(50)", false),
        "date" => array("DATETIME NOT NULL"),
        "nb_users" => array("INT", false),
        "max_nb_users" => array("INT", false),
        "nb_draw" => array("INT", false),
        "initial_score" => array("INT(5)", false),
        "nb_pairs" => array("INT(5)", false),
        "max_nb_pairs" => array("INT(5)",false),
        "status" => array("CHAR(3)", false),
        "filter" => array("CHAR(3)", false),
        "primary" => "id"
    );

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
    public $drawlist = array();

    /**
     * Constructor
     * @param AppDb $db
     * @param null $file_id
     */
    function __construct(AppDb $db, $file_id = null) {
        parent::__construct($db, "DrawRef", $this->table_data);
        if (null != $file_id) {
            self::get($file_id);
        }
    }

    /**
     * Create a reference drawing
     * @param $file_id
     * @param $file
     * @return bool|string
     */
    public function make($file_id,$file) {
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
        $content = $this->parsenewdata($class_vars,array(),array('drawlist'));

        // Add an entry in the ref_drawings table
        $this->db -> addcontent($this->tablename,$content);

        // Create corresponding tables
        self::create_reftable();
        self::get($this->file_id);
        return $this->filename;
	}

    /**
     * Create picture folders corresponding to this reference drawing
     * @return bool
     */
    private function make_folders() {
        $ref_directory = PATH_TO_IMG."$this->file_id/";
        $img_directory = PATH_TO_IMG."$this->file_id/img/";
        $thumb_directory = PATH_TO_IMG."$this->file_id/thumb/";
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

    /**
     * Update DB
     * @param $post
     * @param null $file_id
     * @return string
     */
    public function update($post,$file_id=null) {

        if (null!=$file_id) {
            $this->$file_id = $file_id;
        } elseif (array_key_exists('id',$post)) {
            $this->$file_id = $_POST['id'];
        }

        $class_vars = get_class_vars("DrawRef");
        $content = $this->parsenewdata($class_vars,$post,array('drawlist'));
        if ($this->db->updatecontent($this->tablename,$content,array("file_id"=>$this->file_id))) {
            return 'updated';
        } else {
            return false;
        }
    }

    /**
     * Get information from DB
     * @param $file_id
     * @return bool
     */
    public function get($file_id) {

        if (self::exists($file_id)) {
            $sql = "SELECT * FROM $this->tablename WHERE file_id='$file_id'";
            $req = $this->db->send_query($sql);
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

    /**
     * Check if ref drawing exists in the database (return true if it exists)
     * @param $file_id
     * @return bool
     */
    public function exists($file_id) {
        $reflist = self::get_refdrawinglist();
        return in_array($file_id,$reflist);
    }

    /**
     * Get number of participants
     * @return int
     */
    private function getnbusers() {
        return count(self::getusers());
	}

    /**
     * Get maximum number of possible pairs
     * @return float
     */
    private function getmaxnbpairs() {
        return factorial($this->nb_draw)/(factorial($this->nb_draw-2)*factorial(2));
    }

    /**
     * Get users list
     * @param string $property
     * @return array
     */
    function getusers($property = "userid") {
        $reftable = $this->db->dbprefix.'_'.$this->file_id."_users";
        $sql = "SELECT $property FROM $reftable";
        $req = $this->db->send_query($sql);
        $users = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $users[] = $row[$property];
        }
        return $users;
    }

    /**
     * Make an unique ID
     * @return string
     */
    function makeID() {
        $file_id = $this->file_id."_".rand(1,10000);

        // Check if random ID does not already exist in our database
        $prev_id = $this->db->getinfo($this->tablename,'file_id');
        while (in_array($file_id,$prev_id)) {
            $file_id = $this->file_id."_".rand(1,10000);
        }
        return $file_id;
	}

    /**
     * Upload reference file
     * @param $file
     * @return string
     */
    function upload($file) {

		if (isset($file['tmp_name']) && !empty($file['name'])) {
            $tmp = htmlspecialchars($file['tmp_name']);
            $splitname = explode(".", strtolower($file['name']));
            $extension = end($splitname);

			$img_directory = PATH_TO_IMG."$this->file_id/img/";
            $thumb_directory = PATH_TO_IMG."$this->file_id/thumb/";
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

    /**
     * Get list of items corresponding to the current object
     * @param string $property
     * @return mixed
     */
    function get_refdrawinglist($property="file_id") {
        $refdrawlist = $this->db->getinfo($this->tablename,$property);
		return $refdrawlist;
	}

    /**
     * Get list of items corresponding to the current object
     * @param null $filter
     * @return array
     */
    function get_drawingslist($filter=null) {
        $reftable = $this->db->dbprefix.'_'.$this->file_id."_ranking";
        $sql = "SELECT file_id FROM $reftable";
        if (null != $filter) {
            $sql .= " ORDER BY $filter DESC";
        }
        $req = $this->db->send_query($sql);
        $drawlist = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $drawlist[] = $row['file_id'];
        }
        return $drawlist;
	}

    /**
     * Select a reference drawing for the current user
     * @param $user_ip
     * @return bool|string
     */
    public function selectdrawref($user_ip) {
        $reflist = self::get_refdrawinglist();

        // Update all ref drawings tables
        foreach ($reflist as $ref_id) {
            $ref = new DrawRef($this->db, $ref_id);
        }

        $sql = "SELECT file_id FROM $this->tablename WHERE status='on' and nb_users<max_nb_users and nb_draw>='2' ORDER BY nb_users ASC";
        $req = $this->db->send_query($sql);
        $validref = false;
        while ($row = mysqli_fetch_assoc($req)) {
            $ref_id = $row['file_id'];
            $cur_ref = new DrawRef($this->db, $ref_id);

            // Check if user already exists for this ref drawing and if this ref drawing's settings allow users to do it again
            $user_exist = $cur_ref->checkuser($user_ip);
            if ($user_exist == false || ($user_exist == true && $cur_ref->filter == "off")) {
                $validref = $cur_ref->file_id;
                return $validref;
            }
        }
        return $validref;
    }

    /**
     * Check if user exists (ip checking)
     * @param $user_ip
     * @return bool
     */
    public function checkuser($user_ip) {
        $users = self::getusers('ip');
        return in_array($user_ip, $users);
    }

    /**
     * Delete current object (database &  corresponding files)
     * @return bool
     */
    function delete() {

        // Delete corresponding entry in the ref_drawings table
        $this->db->deletecontent($this->tablename,array("file_id"),array($this->file_id));

        // Delete corresponding tables
        $tablenames = self::get_tables();
        foreach ($tablenames as $table_name) {
            $this->db -> deletetable($table_name);
        }

        // Delete related files
        $img_path = PATH_TO_IMG."$this->file_id/";
        deleteDirectory($img_path);
        return true;
    }

    /**
     * Get tables name
     * @return array
     */
    public function get_tables() {
        $table_name = $this->db->dbprefix.'_'.$this->file_id."_%";
        $sql = "SHOW TABLES FROM ".$this->db->dbname." LIKE '$table_name'";
        $req = $this->db->send_query($sql);
        $result = array();
        while ($row = mysqli_fetch_array($req)) {
            $result [] = $row[0];
        }
        return $result;
    }

    /**
     * Create related tables in the database
     */
    function create_reftable() {

		$tables = array(
			"table1" => $this->db->dbprefix.'_'.$this->file_id."_users",
			"table2" => $this->db->dbprefix.'_'.$this->file_id."_ranking",
			"table3" => $this->db->dbprefix.'_'.$this->file_id."_comp_mat",
			"table4" => $this->db->dbprefix.'_'.$this->file_id."_res_mat",
            "table5" => $this->db->dbprefix.'_'.$this->file_id."_content");

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
            $this->db->createtable($table_name,$cols_name,1);
        }

        // Add default content
        $Appconfig = new AppConfig($this->db);
        $this->db->addcontent($tables['table5'],array('type'=>'instruction','lang'=>'en','content'=>$Appconfig->instruction));
        $this->db->addcontent($tables['table5'],array('type'=>'consent','lang'=>'en','content'=>$Appconfig->consent));
    }

    /**
     * Display items corresponding to the current object
     * @param null $filter
     * @return string
     */
    public function displayitems($filter=null) {
        $drawlist = self::get_drawingslist($filter);
        $AppConfig = new AppConfig($this->db);

        $content = "";
        if (!empty($drawlist)) {

            foreach ($drawlist as $id) {
                $item = new Ranking($this->db,$this->file_id,$id);
                $thumb_url = "../images/$item->refid/thumb/thumb_$item->filename";
                $del_url = "../images/delete.png";
                $content .= "
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
            $content = "<span id='warning'>There are no items for this reference drawing yet</span>";
        }

        $result = "<div class='upl_container' id='$this->file_id'>
               <div class='upl_form'>
                    <form method='post' enctype='multipart/form-data'>
                        <input type='file' name='item,$this->file_id'  class='upl_input' id='$this->file_id' multiple style='display: none;' />
                        <div class='upl_btn' id='$this->file_id'>
                            Drag your files
                            <div class='upl_filetypes'>($AppConfig->upl_types)</div>
                            <div class='upl_errors'></div>
                        </div>
                    </form>
               </div>
                <div class='upl_filelist'>$content</div>
            </div>";

        return $result;
    }

    public function showDetails() {
        $itemlist = $this->displayitems();

        $imgurl = "../images/$this->file_id/thumb/thumb_$this->filename";

        $sort_option =  "
                <label for='order'>Sort by</label>
	            <select name='order' class='sortitems' data-ref='$this->file_id'>
	            	<option value='' selected></option>
	            	<option value='score'>Score</option>
	            	<option value='file_id'>File ID</option>
	            	<option value='nb_occ'>Number of users</option>
	        	</select>";

        $content = "
	    <div class='refdraw-div' id='$this->file_id'>

	        <div style='width: 100%; margin: auto;'>
                <div class='refdraw-name'>$this->file_id</div>
                <div class='refdraw-delbutton'>
                <input type='submit' value='Delete' id='submit' data-ref='$this->file_id' class='deleteref'/>
                </div>
	        </div>

	        <div class='refdraw-content'>
                <div class='refdraw-desc'>
                    <div class='refdraw-thumb'>
                        <img src='$imgurl' class='ref-thumb'>
                    </div>

                    <div class='refdraw-info'>
                        <p>Number of drawings: $this->nb_draw</p>
                        <p>Number of users: $this->nb_users</p>
                    </div>

                </div>

                <div class='refdraw-half'>
                    $sort_option
                    <div class='itemList' id='$this->file_id'>$itemlist</div>
                </div>
            </div>

	    </div>";
        return $content;
    }

    /**
     * Get instructions and consent forms related to the current object
     * @param $type
     * @return array
     */
    public function get_content($type) {
        $reftable = $this->db->dbprefix.'_'.$this->file_id.'_content';
        $sql = "SELECT lang,content FROM $reftable WHERE type='$type'";
        $req = $this->db->send_query($sql);
        $content = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $content[$row['lang']] = htmlspecialchars_decode($row['content']);
        }
        return $content;
    }

    /**
     * Get content languages
     * @return array
     */
    public function getlanguages() {
        $reftable = $this->db->dbprefix.'_'.$this->file_id.'_content';
        $sql = "SELECT lang FROM $reftable WHERE type='instruction'";
        $req = $this->db->send_query($sql);
        $languages = array();
        while ($row = mysqli_fetch_assoc($req)) {
            $languages[] = $row['lang'];
        }
        return $languages;
    }

}

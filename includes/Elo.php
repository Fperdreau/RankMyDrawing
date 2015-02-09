<?php

    require_once($_SESSION['path_to_includes'].'includes.php');

	class Elo {

		public $file_id = "";
        public $filename = "";
        public $date = "";
		public $score = "";
        public $nb_win = "";
        public $nb_occ = "";
		public $rank =  "";
		public $refid = "";

        // Constructor
        function __construct($refid=null,$file_id=null) {
            if (null != $file_id) {
                self::get($refid,$file_id);
            }
        }

        function make($refid,$file) {
            require($_SESSION['path_to_app'].'admin/conf/config.php');

            $this->refid = $refid;
            $this->file_id = self::makeID();
            $this->filename = self::upload($file);
            $this->date = date('Y-m-d');

            // Create corresponding tables
            self::add();
            self::get($this->refid,$this->file_id);
            return $this->filename;
        }

        // Get information
        public function get($refid,$file_id) {
            require($_SESSION['path_to_app'].'admin/conf/config.php');
            $db_set = new DB_set();
            $this->refid = $refid;
            $this->file_id = $file_id;
            $table = $db_set->dbprefix.$this->refid.'_ranking';
            $sql = "SELECT * FROM $table WHERE file_id='$file_id'";
            $req = $db_set->send_query($sql);
            $class_vars = get_class_vars("Elo");
            $row = mysqli_fetch_assoc($req);

            if (!empty($row)) {
                foreach ($row as $varname=>$value) {
                    if (array_key_exists($varname,$class_vars)) {
                        $this->$varname = $value;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        function add() {
            require($_SESSION['path_to_app'].'admin/conf/config.php');
            $db_set = new DB_set();

            // Tables information
            $table_prefix = $this->refid;
            $tables = array(
                "table1" => $db_set->dbprefix.$table_prefix."_users",
                "table2" => $db_set->dbprefix.$table_prefix."_ranking",
                "table3" => $db_set->dbprefix.$table_prefix."_comp_mat",
                "table4" => $db_set->dbprefix.$table_prefix."_res_mat");
            $ntable = count($tables);

            // Update ranking table
            $initial_score = $db_set->getinfo($config_table,"value",array("variable"),array("'initial_score'"));

            $db_set -> addcontent($tables["table2"],"file_id,filename,date,score","'$this->file_id','$this->filename','$this->date','$initial_score'");

            // Update other tables
            for ($t=3;$t<=$ntable;$t++) {
                $table_name = $tables["table".$t];

                // add field if not present
                $sql = "ALTER TABLE $table_name ADD $this->file_id INT NOT NULL";
                $db_set->send_query($sql);

                $db_set -> addcontent($table_name,"file_id","'$this->file_id'");
            }
        }

        // Upload items files
        function upload($file) {
            $img_directory = $_SESSION['path_to_app']."images/$this->refid/img/";
            $thumb_directory = $_SESSION['path_to_app']."images/$this->refid/thumb/";

            $tmp = $file['tmp_name'];
            $initfilename = $file['name'];
            $split = explode(".", $initfilename);
            $extension = end($split);

            // Make an unique ID for this item
            $newname = $this->file_id.".".$extension;

            if (upload_img($tmp,$img_directory,$newname)) {
                if (upload_thumb($newname,$img_directory,$thumb_directory,100)) {
                    return $newname;
                } else {
                    return "thumb not uploaded";
                }
            } else {
                return "file not uploaded";
            }
        }

        // Delete item
        function delete() {
            require($_SESSION['path_to_app'].'admin/conf/config.php');
            // Connect to db
            $db_set = new DB_set();

            // Tables information
            $table_prefix = $this->refid;
            $tables = array(
                "table1" => $db_set->dbprefix.$table_prefix."_users",
                "table2" => $db_set->dbprefix.$table_prefix."_ranking",
                "table3" => $db_set->dbprefix.$table_prefix."_comp_mat",
                "table4" => $db_set->dbprefix.$table_prefix."_res_mat");
            $ntable = count($tables);

            $db_set -> deletecontent($tables["table2"],array("file_id"),array("'$this->file_id'"));

            // Update other tables
            for ($t=3;$t<=$ntable;$t++) {
                $table_name = $tables["table".$t];

                // add field if not present
                $sql = "ALTER TABLE $table_name DROP COLUMN $this->file_id";
                $db_set->send_query($sql);

                $db_set -> deletecontent($table_name,array("file_id"),array("'$this->file_id'"));
            }

            // Delete file
            $img_url = $_SESSION['path_to_app']."images/$this->refid/img/$this->filename";
            $thumb_url = $_SESSION['path_to_app']."images/$this->refid/thumb/thumb_$this->filename";

            if (is_file($img_url)) {
                unlink($img_url);
            }

            if (is_file($thumb_url)) {
                unlink($thumb_url);
            }
            return true;
        }

        // Make an unique ID
        function makeID() {
            require($_SESSION['path_to_app']."/admin/conf/config.php");
            $db_set = new DB_set();
            $file_id = $this->refid."_".rand(1,10000);

            // Check if random ID does not already exist in our database
            $prev_id = $db_set->getinfo($ref_drawings_table,'file_id');
            while (in_array($file_id,$prev_id)) {
                $file_id = $this->refid."_".rand(1,10000);
            }
            return $file_id;
        }

        // Update ELO score
        public function updateELO($outcome,$oppid) {
            $opp = new ELO($oppid);
            $W = $outcome;
            $diff = $this->score - $opp->score; // Difference of scores
            $pwin = 1/(1 + (pow(10, -$diff/400))); // Win probability
            $coef = 800/(2*($this->nb_occ+1)); // Adjust coefficient to the item's level
            $this->score = $this->score + $coef * ($outcome - $pwin); // Compute new ELO score
        }

        // Write match results in the database
		public function updateresults($oppid,$outcome) {
            $this->nb_occ++;
            $this->nb_win += $outcome;
			$db_set = new DB_set();

            // Update ranking table
            $rankingtable = $db_set->dbprefix.$this->refid."_ranking";
            $db_set->updatecontent($rankingtable,array("nb_win","nb_occ","score"),array($this->nb_win,$this->nb_occ,$this->score),array("file_id"),array("'$this->file_id'"));

            // Update result matrix
            $restable = $db_set->dbprefix.$this->refid."_res_mat";
            $oldvalue = $db_set->getinfo($restable,$oppid,"file_id",$this->file_id);
            $newvalue = $oldvalue[0] + $outcome;
            $db_set->updatecontent($restable,"$oppid","'$newvalue'",array("file_id"),array("'$this->file_id'"));

            // Update comparison matrix
            $comptable = $db_set->dbprefix.$this->refid."_comp_mat";
            $oldvalue = $db_set->getinfo($restable,$oppid,"file_id",$this->file_id);
            $newvalue = $oldvalue[0] + 1;
            $db_set->updatecontent($comptable,"$oppid","'$newvalue'",array("file_id"),array("'$this->file_id'"));

            return true;
        }
    }

?>

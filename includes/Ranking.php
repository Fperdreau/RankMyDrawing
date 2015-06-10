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


class Ranking {

    private $db;
    public $file_id = "";
    public $filename = "";
    public $date = "";
    public $score = "";
    public $nb_win = "";
    public $nb_occ = "";
    public $rank =  "";
    public $refid = "";

    /**
     * Constructor
     * @param AppDb $db
     * @param null $refid
     * @param null $file_id
     */
    function __construct(AppDb $db, $refid=null,$file_id=null) {
        $this->db = $db;
        if (null != $file_id) {
            self::get($refid,$file_id);
        }
    }

    /**
     * Create new item
     * @param $refid
     * @param $file
     * @return string
     */
    function make($refid,$file) {
        $this->refid = $refid;
        $this->file_id = self::makeID();
        $result = self::upload($file);

        if ($result['status'] == true) {
            $this->filename = $result['msg'];
            $this->date = date('Y-m-d H:i:s');
            // Create corresponding tables
            self::add();
            self::get($this->refid,$this->file_id);
            return $this->filename;
        } else {
            return false;
        }
    }

    /**
     * Retrieve item's information from db
     * @param $refid
     * @param $file_id
     * @return bool
     */
    public function get($refid,$file_id) {
        $this->refid = $refid;
        $this->file_id = $file_id;
        $table = $this->db->dbprefix.'_'.$this->refid.'_ranking';
        $sql = "SELECT * FROM $table WHERE file_id='$file_id'";
        $req = $this->db->send_query($sql);
        $class_vars = get_class_vars("Ranking");
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

    /**
     * Create item's corresponding tables
     */
    function add() {
        // Tables information
        $tables = array(
            "table1" => $this->db->dbprefix.'_'.$this->refid."_users",
            "table2" => $this->db->dbprefix.'_'.$this->refid."_ranking",
            "table3" => $this->db->dbprefix.'_'.$this->refid."_comp_mat",
            "table4" => $this->db->dbprefix.'_'.$this->refid."_res_mat");
        $ntable = count($tables);

        // Update ranking table
        $initial_score = $this->db->getinfo($this->db->tablesname['AppConfig'],"value",array("variable"),array("'initial_score'"));
        $this->db -> addcontent($tables["table2"],array("file_id"=>$this->file_id,'filename'=>$this->filename,'date'=>$this->date,'score'=>$initial_score));

        // Update other tables
        for ($t=3;$t<=$ntable;$t++) {
            $table_name = $tables["table".$t];

            // add field if not present
            $sql = "ALTER TABLE $table_name ADD $this->file_id INT NOT NULL";
            $this->db->send_query($sql);

            $this->db -> addcontent($table_name,array("file_id"=>$this->file_id));
        }
    }

    // Upload items files
    /**
     * Upload item's corresponding files
     * @param $file
     * @return string
     */
    function upload($file) {
        $img_directory = PATH_TO_IMG."$this->refid/img/";
        $thumb_directory = PATH_TO_IMG."$this->refid/thumb/";
        $tmp = $file['tmp_name'][0];
        $initfilename = $file['name'][0];
        $split = explode(".", $initfilename);
        $extension = end($split);

        // Make an unique ID for this item
        $newname = $this->file_id.".".$extension;

        if (upload_img($tmp,$img_directory,$newname)) {
            if (upload_thumb($newname,$img_directory,$thumb_directory,100)) {
                $result['msg'] = $newname;
                $result['status'] = true;
            } else {
                $result['status'] = false;
                $result['msg'] = "thumb not uploaded";
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "file not uploaded";
        }
        return $result;
    }

    /**
     * Delete item and its corresponding database entries
     * @return bool
     */
    function delete() {
        // Tables information
        $tables = array(
            "table1" => $this->db->dbprefix.'_'.$this->refid."_users",
            "table2" => $this->db->dbprefix.'_'.$this->refid."_ranking",
            "table3" => $this->db->dbprefix.'_'.$this->refid."_comp_mat",
            "table4" => $this->db->dbprefix.'_'.$this->refid."_res_mat");
        $ntable = count($tables);

        $this->db -> deletecontent($tables["table2"],array("file_id"),array($this->file_id));

        // Update other tables
        for ($t=3;$t<=$ntable;$t++) {
            $table_name = $tables["table".$t];

            // add field if not present
            $sql = "ALTER TABLE $table_name DROP COLUMN $this->file_id";
            $this->db->send_query($sql);

            $this->db -> deletecontent($table_name,array("file_id"),array($this->file_id));
        }

        // Delete file
        $img_url = PATH_TO_IMG."$this->refid/img/$this->filename";
        $thumb_url = PATH_TO_IMG."$this->refid/thumb/thumb_$this->filename";

        if (is_file($img_url)) {
            unlink($img_url);
        }

        if (is_file($thumb_url)) {
            unlink($thumb_url);
        }
        return true;
    }

    /**
     * Create item's ID
     * @return string
     */
    function makeID() {
        $file_id = $this->refid."_".rand(1,10000);

        // Check if random ID does not already exist in our database
        $prev_id = $this->db->getinfo($this->db->tablesname['DrawRef'],'file_id');
        while (in_array($file_id,$prev_id)) {
            $file_id = $this->refid."_".rand(1,10000);
        }
        return $file_id;
    }

    /**
     * Update ELO ranking
     * @param $outcome
     * @param $oppid
     */
    public function updateELO($outcome,$oppid) {
        $opp = new Ranking($this->db,$oppid);
        $W = $outcome;
        $diff = $this->score - $opp->score; // Difference of scores
        $pwin = 1/(1 + (pow(10, -$diff/400))); // Win probability
        $coef = 800/(2*($this->nb_occ+1)); // Adjust coefficient to the item's level
        $this->score = $this->score + $coef * ($outcome - $pwin); // Compute new ELO score
    }

    /**
     * Write match's outcome to the database
     * @param $oppid
     * @param $outcome
     * @return bool
     */
    public function updateresults($oppid,$outcome) {
        $this->nb_occ++;
        $this->nb_win += $outcome;

        // Update ranking table
        $rankingtable = $this->db->dbprefix.'_'.$this->refid."_ranking";
        $this->db->updatecontent($rankingtable,array("nb_win"=>$this->nb_win,"nb_occ"=>$this->nb_occ,"score"=>$this->score),
            array("file_id"=>$this->file_id));

        // Update result matrix
        $restable = $this->db->dbprefix.'_'.$this->refid."_res_mat";
        $oldvalue = $this->db->getinfo($restable,$oppid,"file_id",$this->file_id);
        $newvalue = $oldvalue[0] + $outcome;
        $this->db->updatecontent($restable,array("$oppid"=>$newvalue),array("file_id"=>$this->file_id));

        // Update comparison matrix
        $comptable = $this->db->dbprefix.'_'.$this->refid."_comp_mat";
        $oldvalue = $this->db->getinfo($restable,$oppid,"file_id",$this->file_id);
        $newvalue = $oldvalue[0] + 1;
        $this->db->updatecontent($comptable,array("$oppid"=>$newvalue),array("file_id"=>$this->file_id));

        return true;
    }
}

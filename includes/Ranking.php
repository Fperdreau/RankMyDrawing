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
    public $file_id;
    public $filename;
    public $date;
    public $score;
    public $nb_win;
    public $nb_occ;
    public $rank;
    public $refid;

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

        $result = $this->upload($file);
        if ($result['error'] !== true) {
            return $result;
        } else {
            $this->filename = $result['status'];
            $this->date = date('Y-m-d H:i:s');
            // Create corresponding tables
            self::add();
            self::get($this->refid,$this->file_id);
        }
        return $result;
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
        $this->db->addcontent($tables["table2"],array("file_id"=>$this->file_id,'filename'=>$this->filename,'date'=>$this->date,'score'=>$initial_score));

        // Update other tables
        for ($t=3;$t<=$ntable;$t++) {
            $table_name = $tables["table".$t];

            // add field if not present
            $sql = "ALTER TABLE $table_name ADD $this->file_id INT NOT NULL";
            $this->db->send_query($sql);

            $this->db->addcontent($table_name,array("file_id"=>$this->file_id));
        }
    }

    /**
     * Upload item's corresponding files
     * @param $file
     * @return string
     */
    function upload($file) {
        $upload = new Uploads($this->db);
        $img_directory = PATH_TO_IMG."$this->refid/img/";
        $thumb_directory = PATH_TO_IMG."$this->refid/thumb/";

        $result = $upload->make($file,$img_directory,$this->file_id);
        if ($result['error'] === true) {
            $upload->get($result['status']);
            if (upload_thumb($upload->filename,$img_directory,$thumb_directory,100)) {
                $result['error'] = true;
            } else {
                $result['error'] = false;
                $result['status'] = "thumb not uploaded";
            }
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

        $result['status'] = $this->db -> deletecontent($tables["table2"],array("file_id"),array($this->file_id));

        // Update other tables
        for ($t=3;$t<=$ntable;$t++) {
            $table_name = $tables["table".$t];

            // add field if not present
            $sql = "ALTER TABLE $table_name DROP COLUMN $this->file_id";
            $this->db->send_query($sql);

            $result['status'] = $this->db->deletecontent($table_name,array("file_id"),array($this->file_id));
        }

        // Delete file
        $file = new Uploads($this->db, $this->filename);
        $result = $file->delete();

        // Delete thumb
        $thumb_url = PATH_TO_IMG."$this->refid/thumb/thumb_$file->filename";
        if (is_file($thumb_url)) {
            unlink($thumb_url);
        }
        return $result;
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
     * @param $oppid: opponent ID
     * @param $outcome: match outcome
     * @return bool
     */
    public function updateresults($oppid,$outcome) {
        $this->nb_occ++;
        $this->nb_win += $outcome;

        // Update ranking table
        $rankingTable = $this->db->dbprefix.'_'.$this->refid."_ranking";
        $this->db->updatecontent($rankingTable,array("nb_win"=>$this->nb_win,"nb_occ"=>$this->nb_occ,"score"=>$this->score),
            array("file_id"=>$this->file_id));

        // Update result matrix
        $resTable = $this->db->dbprefix.'_'.$this->refid."_res_mat";
        $sql = "SELECT $oppid FROM $resTable WHERE file_id='$this->file_id'";
        $req = $this->db->send_query($sql);
        $oldResult = mysqli_fetch_assoc($req);
        $newResult = $oldResult[$oppid] + $outcome;
        $this->db->updatecontent($resTable,array($oppid=>$newResult),array("file_id"=>$this->file_id));

        // Update comparison matrix
        $compTable = $this->db->dbprefix.'_'.$this->refid."_comp_mat";
        $sql = "SELECT $oppid FROM $compTable WHERE file_id='$this->file_id'";
        $req = $this->db->send_query($sql);
        $oldValue = mysqli_fetch_assoc($req);
        $newValue = $oldValue[$oppid] + 1;
        $this->db->updatecontent($compTable,array($oppid=>$newValue),array("file_id"=>$this->file_id));

        return true;
    }

    /**
     * Show item details (in modal window)
     */
    public function showDetails() {
        // Add a delete link (only for admin and organizers or the authors)
        $file = new Uploads($this->db,$this->filename);
        $img = "../images/$this->refid/img/$file->filename";
        return "
        <div class='item_img' style='background: url($img) no-repeat; background-size: 600px;'>
            <div class='item_caps'>
                <div id='item_title'>$this->file_id</div>
                <span style='color:#CF5151; font-weight: bold;'>Score: </span>$this->score<br>
                <span style='color:#CF5151; font-weight: bold;'>Number of matchs: </span>$this->nb_occ<br>
                <span style='color:#CF5151; font-weight: bold;'>Number of win: </span>$this->nb_win<br>
            </div>
        </div>
        <div class='del_item' data-ref='$this->refid' data-item='$this->file_id'>Delete</div>
        ";
    }

    /**
     * Show as thumb
     */
    public function showThumb() {
        $file = new Uploads($this->db, $this->filename);
        $thumb_url = "../images/$this->refid/thumb/thumb_$file->filename";
        return "
            <div class='item' id='item_$this->file_id'>
                <div style='font-size: 12px; float: left;'>Score: $this->score</div>
                <div class='delete_btn_item' id='$this->file_id' data-item='$this->refid'>
                </div>
                <div class='thumb leanModal' data-modal='item_modal' data-section='item_description' data-ref='$this->refid'
                 data-item='$this->file_id'>
                    <img src='$thumb_url' class='thumb' alt='$this->file_id'>
                </div>
            </div>
        ";
    }
}

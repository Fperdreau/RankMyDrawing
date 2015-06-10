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

class Participant extends AppTable {

    protected $table_data = array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "date" => array("DATETIME", false),
        "userid" => array("CHAR(20)", false),
        "refid" => array("CHAR(20)", false),
        "name" => array("CHAR(5)", false),
        "email" => array("CHAR(50)", false),
        "ip" => array("CHAR(20)", false),
        "nb_visit" => array("INT(2)", false),
        "age" => array("INT(3)", false),
        "gender" => array("CHAR(10)", false),
        "language" => array("CHAR(5)", false),
        "drawlvl" => array("CHAR(10)", false),
        "artint" => array("CHAR(3)", false),
        "response1" => array("TEXT", false),
        "response2" => array("TEXT", false),
        "pair1" => array("TEXT", false),
        "pair2" => array("TEXT", false),
        "time_start" => array("TIMESTAMP", false),
        "time_end" => array("TIMESTAMP", false),
        "primary" => "id");

    public $date = "";
    public $userid = "";
    public $refid = "";
    public $name = "";
    public $email = "";
    public $ip = "";
    public $nb_visit = "";
    public $age = "";
    public $gender = "";
    public $language = "";
    public $drawlvl = "";
    public $artint = "";
    public $response1 = "";
    public $response2 = "";
    public $pair1 = "";
    public $pair2 = "";
    public $time_start = "";
    public $time_end = "";

    /**
     * Constructor
     * @param AppDb $db
     * @param null $userid
     * @param null $refid
     */
    function __construct(AppDb $db, $userid=null,$refid=null) {
        parent::__construct($db, 'Participant',$this->table_data, $refid.'_users');
        if ($userid != null) {
            self::get($userid,$refid);
        }
    }

    /**
     * Create User
     * @param $post
     * @param $refid
     * @return string
     */
    function make($post,$refid) {
        $this->refid = $refid;
        $post['date'] = date("Y-m-d H:i:s");
        $post['ip'] = self::getip();
        $post['userid'] = self::makeID();

        // Parse variables and values to store in the table
        $class_vars = get_class_vars("Participant");
        $class_keys = array_keys($class_vars);
        $content = array();
        foreach ($post as $name => $value) {
            if (in_array($name, $class_keys)) {
                $this->$name = $value;
                $escaped = $this->db->escape_query($value);
                $content[$name] = $escaped;
            }
        }
        // Add user to the database
        $result = $this->db->addcontent($this->db->dbprefix.'_'.$refid."_users",$content);
        return $this->userid;
    }

    /**
     * Update user info
     * @return bool
     */
    function update() {
        $users_table = $this->db->dbprefix.'_'.$this->refid.'_users';
        foreach ($this as $key=>$value) {
            if (!in_array($key, array("db","tablename",'table_data'))) {
                $this->db->updatecontent($users_table, array($key => $value), array("userid" => $this->userid));
            }
        }
        return true;
    }

    /**
     * Get IP
     * @return string
     */
    public function getip() {
        return strval($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Make User ID
     * @return string
     */
    function makeID() {
        $id = $this->refid."_".rand(1,10000);
        // Check if random ID does not already exist in our database
        $prev_id = $this->db->getinfo($this->db->dbprefix.'_'.$this->refid.'_users','id');
        while (in_array($id,$prev_id)) {
            $id = $this->refid."_".rand(1,10000);
        }
        return $id;
    }

    /**
     * Get user info
     * @param $userid
     * @param $refid
     * @return bool
     */
    function get($userid,$refid) {
        $users_table = $this->db->dbprefix.'_'.$refid.'_users';
        $sql = "SELECT * FROM $users_table WHERE userid='$userid'";

        $req = $this->db->send_query($sql);
        $data = mysqli_fetch_assoc($req);
        if (!empty($data)) {
            foreach ($data as $varname=>$value) {
                $this->$varname = $value;
            }
            return true;
        } else {
            return false;
        }
    }
}

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

class participant {
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

    function __construct($userid=null,$refid=null) {
        if ($userid != null) {
            self::get($userid,$refid);
        }
    }

    // Create user

    function make($post,$refid) {
        $db_set = new DB_set();

        $this->refid = $refid;
        $post['date'] = date("Y-m-d H:i:s");
        $post['ip'] = self::getip();
        $post['userid'] = self::makeID();

        // Parse variables and values to store in the table
        $class_vars = get_class_vars("participant");
        $class_keys = array_keys($class_vars);
        $values = array();
        $variables = array();
        foreach ($post as $name => $value) {
            if (in_array($name,$class_keys)) {
                $this->$name = $value;
                $escaped = mysqli_real_escape_string($db_set->bdd,$value);
                $values[] = "'$escaped'";
                $variables[] = $name;
            }
        }
        $values = implode(",", $values);
        $variables = implode(",", $variables);

        // Add user to the database
        $result = $db_set->addcontent($db_set->dbprefix.$refid."_users",$variables,$values);
        return $this->userid;
    }

    function update() {
        $db_set = new DB_set();
        $users_table = $db_set->dbprefix.$this->refid.'_users';
        foreach ($this as $key=>$value) {
            $db_set->updatecontent($users_table,$key,"'$value'",array("userid"),array("'$this->userid'"));
        }
        return true;
    }

    public function getip() {
        return strval($_SERVER['REMOTE_ADDR']);
    }

    // Make an unique ID
    function makeID() {
        $db_set = new DB_set();
        $id = $this->refid."_".rand(1,10000);

        // Check if random ID does not already exist in our database
        $prev_id = $db_set->getinfo($db_set->dbprefix.$this->refid.'_users','id');
        while (in_array($id,$prev_id)) {
            $id = $this->refid."_".rand(1,10000);
        }
        return $id;
    }

    function get($userid,$refid) {
        $db_set = new DB_set();
        $users_table = $db_set->dbprefix.$refid.'_users';
        $class_vars = get_class_vars("participant");

        $sql = "SELECT * FROM $users_table WHERE userid='$userid'";

        $req = $db_set -> send_query($sql);
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

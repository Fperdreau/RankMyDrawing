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

/**
 * Class AppConfig
 *
 * Handles application configuration information and routines (updates, get).
 */
class AppConfig extends AppTable {

    protected $table_data = array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "variable" => array("CHAR(20)", false),
        "value" => array("TEXT", false),
        "primary" => "id");
    /**
     * Application info
     *
     */
    public $status = 'On';
    public $app_name = "RankMyDrawings";
    public $version = "1.3.1";
    public $author = "Florian Perdreau";
    public $repository = "https://github.com/Fperdreau/RankMyDrawings";
    public $sitetitle = "RankMyDrawings";
    public $site_url = "(e.g. http://www.mydomain.com/Pjc/)";
    public $clean_day = 10;
    public $max_nb_attempt = 5; // Maximum nb of login attempt

    /**
     * Experiment's settings
     */
    public $expon = 0;
    public $initial_score = 1500;
    public $npair = 0;
    public $filter = "on";
    public $languages = array();
    public $redirecturl = "https://github.com/Fperdreau/RankMyDrawings";
    public $instruction = "";
    public $consent = "";

    /**
     * Mail host information
     *
     */
    public $mail_from = "admin@rankmydrawings.com";
    public $mail_from_name = "RankMyDrawings";
    public $mail_host = "smtp.gmail.com";
    public $mail_port = "465";
    public $mail_username = "";
    public $mail_password = "";
    public $SMTP_secure = "ssl";
    public $pre_header = "[RMD]";

    /**
     * Uploads settings
     *
     */
    public $upl_types = "png";
    public $upl_maxsize = 10000000;

    /**
     * Constructor
     * @param AppDb $db
     * @param bool $get
     */
    public function __construct(AppDb $db,$get=true) {
        parent::__construct($db, 'AppConfig',$this->table_data);
        if ($get) {
            self::get();
        }
    }
    /**
     * Get application settings
     * @return bool
     */
    public function get() {
        $sql = "select variable,value from $this->tablename";
        $req = $this->db->send_query($sql);
        while ($row = mysqli_fetch_assoc($req)) {
            $varname = $row['variable'];
            $value = htmlspecialchars_decode($row['value']);
            $this->$varname = $value;
        }
        return true;
    }

    /**
     * Update application settings
     * @param array $post
     * @return bool
     */
    public function update($post=array()) {
        $class_vars = get_class_vars("AppConfig");
        $postkeys = array_keys($post);

        foreach ($class_vars as $name => $value) {
            if (in_array($name,array("db","tablename","table_data","languages"))) continue;
            $newvalue = (in_array($name,$postkeys)) ? $post[$name]:$this->$name;
            $newvalue = ($name == "session_type") ? json_encode($newvalue):$newvalue;
            $this->$name = $newvalue;

            $exist = $this->db->getinfo($this->tablename,"variable",array("variable"),array("'$name'"));
            if (!empty($exist)) {
                $this->db->updatecontent($this->tablename,array("value"=>$newvalue),array("variable"=>$name));
            } else {
                $this->db->addcontent($this->tablename,array("variable"=>$name,"value"=>$newvalue));
            }
        }
        return true;
    }
}

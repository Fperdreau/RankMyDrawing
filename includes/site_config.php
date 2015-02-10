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

class site_config {
    // Site info
    public $app_name = "RankMyDrawings";
    public $version = "v1.2";
	public $repository = "https://github.com/Fperdreau/RankMyDrawings";
    public $author = "Florian Perdreau";
    public $sitetitle = "RankMyDrawings";
    public $site_url = "(e.g. http://www.mydomain.com/RankMyDrawings/)";
    public $clean_day = 10;
    // Experiment
    public $expon = 0;
    public $languages = array();
    public $redirecturl = "http://www.florianperdreau.fr";
    public $instruction = "";
    public $consent = "";
    // Mail host information
    public $mail_from = "admin@rankmydrawings.com";
    public $mail_from_name = "RankMyDrawings";
    public $mail_host = "smtp.gmail.com";
    public $mail_port = "465";
    public $mail_username = "";
    public $mail_password = "";
    public $SMTP_secure = "ssl";
    public $pre_header = "[RMD]";

    // Constructor
    public function __construct($get = null) {
        if ($get == 'get') {
            self::get_config();
        }
    }

    public function get_config() {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $sql = "select variable,value from $config_table";
        $req = $db_set->send_query($sql);
        $class_vars = get_class_vars("site_config");
        while ($row = mysqli_fetch_assoc($req)) {
            $varname = $row['variable'];
            $value = $row["value"];
            if (array_key_exists($varname,$class_vars)) {
                $this->$varname = $value;
            }
        }
        return true;
    }

    // Update config
    public function update_config($post) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $class_vars = get_class_vars("site_config");
		$class_keys = array_keys($class_vars);
        foreach ($post as $name => $value) {
            if (in_array($name,$class_keys)) {
                $escape_value = htmlspecialchars($value);
				$exist = $db_set->getinfo($config_table,"variable",array("variable"),array("'$name'"));
	            if (!empty($exist)) {
	                $db_set->updatecontent($config_table,"value","'$escape_value'",array("variable"),array("'$name'"));
	            } else {
	            	$db_set->addcontent($config_table,"variable,value","'$name','$escape_value'");
	            }
			}
        }
        self::get_config();
        return true;
    }

    // Get organizers list
    function getadmin($admin=null) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $sql = "SELECT username,password,firstname,lastname,position,email,status FROM $users_table WHERE status='organizer'";
        if (null != $admin) {
        	$sql .= "or status='admin'";
        }

        $req = $db_set -> send_query($sql);
        $user_info = array();
        $cpt = 0;
        while ($row = mysqli_fetch_assoc($req)) {
            $user_info[]= $row;
            $cpt++;
        }
        return $user_info;
    }



}

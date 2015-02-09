<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 29/09/14
 * Time: 09:14
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

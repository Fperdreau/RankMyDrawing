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
require_once($_SESSION['path_to_includes'].'includes.php');

class users {
    public $username = "";
    public $password = "";
    public $email = "";

    function __construct($prov_username=null) {
        if ($prov_username != null) {
            self::getuserinfo($prov_username);
        }
    }

    // Create user
    function create_user($post) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();

        $post['password'] = self::crypt_pwd($post['password']);

		// Parse variables and values to store in the table
        $class_vars = get_class_vars("users");
        $class_keys = array_keys($class_vars);
        $values = array();
        $variables = array();
        foreach ($post as $name => $value) {
            if (in_array($name,$class_keys)) {
                $escape_value = htmlspecialchars($value);
                $values[] = "'$escape_value'";
                $variables[] = $name;
            }
        }
        $values = implode(",", $values);
        $variables = implode(",", $variables);

        if (self :: user_exist($this->username) == false && self :: mail_exist($this->email) == false) {
			// Add to user table
            $db_set->addcontent($users_table,$variables,$values);
            return true;
		} else {
			return false;
		}
    }

    function getuserinfo($prov_username) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $class_vars = get_class_vars("users");

        $db_set = new DB_set();
        $sql = "SELECT * FROM $users_table WHERE username='$prov_username'";
        $req = $db_set -> send_query($sql);
        $data = mysqli_fetch_assoc($req);
        $exist = $db_set->getinfo($users_table,'username',array("username"),array("'$prov_username'"));
        if (!empty($exist)) {
            foreach ($data as $varname=>$value) {
                if (array_key_exists($varname,$class_vars)) {
                    $this->$varname = $value;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    // Update user info
    function updateuserinfo($post) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();

        $class_vars = get_class_vars("users");
        $class_keys = array_keys($class_vars);
        foreach ($post as $name => $value) {
            $value = htmlspecialchars($value);
            if (in_array($name,$class_keys)) {
                $db_set->updatecontent($users_table,"$name","'$value'",array("username"),array("'$this->username'"));
            }
        }
        self::getuserinfo($this->username);
        return true;
    }

    function user_exist($prov_username) {
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        $userslist = $db_set -> getinfo($users_table,'username');
        if (in_array($prov_username,$userslist)) {
            return true;
        } else {
            return false;
        }
    }

    function mail_exist($prov_mail) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        $maillist = $db_set -> getinfo($users_table,'email');

        if (in_array($prov_mail,$maillist)) {
            return true;
        } else {
            return false;
        }
    }

    function create_hash() {
        $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.
        return $hash;
    }

    function check_pwd($password) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require_once($_SESSION['path_to_includes'].'PasswordHash.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        $truepwd = $db_set -> getinfo($users_table,"password",array("username"),array("'$this->username'"));

        $check = validate_password($password, $truepwd);

        if ($check == 1) {
            $this->logged = true;
            return true;
        } else {
            return false;
        }
    }

    function crypt_pwd($password) {
        require_once($_SESSION['path_to_includes'].'PasswordHash.php');
        $hash = create_hash($password);

        return $hash;
    }

    function delete_user() {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        // Delete corresponding entry in the publication table
        $db_set -> deletecontent($users_table,array("username"),array("'$this->username'"));
    }

}

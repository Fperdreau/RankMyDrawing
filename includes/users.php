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

class Users extends AppTable {

    protected $table_data = array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "username" => array("CHAR(50)", false),
        "password" => array("CHAR(50)", false),
        "email" => array("CHAR(50)",false),
        "primary" => "id"
    );

    public $username = "";
    public $password = "";
    public $email = "";

    /**
     * Constructor
     * @param AppDb $db
     * @param null $prov_username
     */
    function __construct(AppDb $db, $prov_username=null) {
        parent::__construct($db, "Users", $this->table_data);
        if ($prov_username != null) {
            self::get($prov_username);
        }
    }

    /**
     * Create user
     * @param $post
     * @return bool
     */
    function make($post) {
        $post['password'] = self::crypt_pwd($post['password']);

		// Parse variables and values to store in the table
        $class_vars = get_class_vars("Users");
        $content = $this->parsenewdata($class_vars,$post);
        if (self :: user_exist($this->username) == false && self :: mail_exist($this->email) == false) {
			// Add to user table
            $this->db->addcontent($this->tablename,$content);
            return true;
		} else {
			return false;
		}
    }

    /**
     * Get user info
     * @param $prov_username
     * @return bool
     */
    function get($prov_username) {
        $class_vars = get_class_vars("Users");

        $sql = "SELECT * FROM $this->tablename WHERE username='$prov_username'";
        $req = $this->db->send_query($sql);
        $data = mysqli_fetch_assoc($req);
        $exist = $this->db->getinfo($this->tablename,'username',array("username"),array("'$prov_username'"));
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

    /**
     * Update user info
     * @param $post
     * @return bool
     */
    function updateuserinfo($post) {
        $class_vars = get_class_vars("Users");
        $class_keys = array_keys($class_vars);
        foreach ($post as $name => $value) {
            $value = htmlspecialchars($value);
            if (in_array($name,$class_keys)) {
                $this->db->updatecontent($this->tablename,"$name","'$value'",array("username"),array("'$this->username'"));
            }
        }
        self::get($this->username);
        return true;
    }

    /**
     * Check if user already exists
     * @param $prov_username
     * @return bool
     */
    function user_exist($prov_username) {
        $userslist = $this->db->getinfo($this->tablename,'username');
        if (in_array($prov_username,$userslist)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if user's provided email already exist in DB
     * @param $prov_mail
     * @return bool
     */
    function mail_exist($prov_mail) {
        $maillist = $this->db->getinfo($this->tablename,'email');

        if (in_array($prov_mail,$maillist)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate random 32 character hash and assign it to a local variable.
     * @return string
     */
    function create_hash() {
        $hash = md5( rand(0,1000) );
        return $hash;
    }

    /**
     * Check password
     * @param $password
     * @return bool
     */
    function check_pwd($password) {
        $truepwd = $this->db->getinfo($this->tablename,"password",array("username"),array("'$this->username'"));
        $check = validate_password($password, $truepwd);
        if ($check == 1) {
            $this->logged = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Encrypt password
     * @param $password
     * @return string
     */
    function crypt_pwd($password) {
        require_once(PATH_TO_INCLUDES.'PasswordHash.php');
        $hash = create_hash($password);
        return $hash;
    }

    /**
     * Delete user from DB
     */
    function delete_user() {
        $this->db->deletecontent($this->tablename,array("username"),array("'$this->username'"));
    }

}

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
        "date" => array("DATETIME", false),
        "username" => array("CHAR(50)", false),
        "password" => array("CHAR(50)", false),
        "email" => array("CHAR(50)",false),
        "status" => array("CHAR(50)",false),
        "hash" => array("CHAR(32)", false),
        "active" => array("INT(1) NOT NULL", 0),
        "attempt" => array("INT(1) NOT NULL", 0),
        "last_login" => array("DATETIME NOT NULL"),
        "primary" => "id"
    );

    public $date;
    public $username;
    public $password;
    public $email;
    public $status;
    public $active;
    public $attempt;
    public $last_login;
    public $hash;

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
        $post['hash'] = $this->make_hash(); // Create an unique hash for this user
        $this->date = date("Y-m-d H:i:s"); // Date of creation (today)

		// Parse variables and values to store in the table
        $class_vars = get_class_vars("Users");
        $content = $this->parsenewdata($class_vars,$post);
        if (self :: user_exist($this->username) == false && self :: mail_exist($this->email) == false) {
			// Add to user table
            $this->db->addcontent($this->tablename,$content);
            $result['status'] = true;
            $result['msg'] = "Your account has been successfully created!";
		} else {
            $result['status'] = false;
            $result['msg'] = "This username/email address already exist in our database";
		}
        return $result;
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
    function update($post) {
        $class_vars = get_class_vars("Users");
        $content = $this->parsenewdata($class_vars,$post);
        $result['status'] = $this->db->updatecontent($this->tablename,$content,array("username"=>$this->username));
        return $result;
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
    function make_hash() {
        $hash = md5( rand(0,1000) );
        return $hash;
    }

    /**
     * Delete user from DB
     */
    function delete_user() {
        $this->db->deletecontent($this->tablename,array("username"),array("'$this->username'"));
    }

    /**
     * User login
     * @param $post
     * @return mixed
     */
    public function login($post) {
        $password = htmlspecialchars($post['password']);
        if ($this->get($this->username) == true) {
            if ($this -> check_pwd($password) == true) {
                $_SESSION['logok'] = true;
                $_SESSION['username'] = $this -> username;
                $_SESSION['status'] = $this -> status;
                $result['msg'] = "Hi $this->username,<br> welcome back!";
                $result['status'] = true;
            } else {
                $_SESSION['logok'] = false;
                $result['status'] = false;
                $attempt = $this->checkattempt();
                if ($attempt == false) {
                    $result['msg'] = "Wrong password. You have exceeded the maximum number
                        of possible attempts, hence your account has been deactivated for security reasons.
                        We have sent an email to your address including an activation link.";
                } else {
                    $result['msg'] = "Wrong password. $attempt login attempts remaining";
                }
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "Wrong username";
        }
        return $result;
    }

    /**
     * Check number of unsuccessful login attempts.
     * Deactivate the user's account if this number exceeds the maximum
     * allowed number of attempts and send an email to the user with an activation link.
     * @return int
     */
    private function checkattempt() {
        $last_login = new DateTime($this->last_login);
        $now = new DateTime();
        $diff = $now->diff($last_login);
        // Reset the number of attempts if last login attempt was 1 hour ago
        $this->attempt = $diff->h >= 1 ? 0:$this->attempt;
        $this->attempt += 1;
        $AppConfig = new AppConfig($this->db);
        if ($this->attempt >= $AppConfig->max_nb_attempt) {
            self::activation(0); // We deactivate the user's account
            $this->send_activation_mail();
            return false;
        }
        $this->last_login = date('Y-m-d H:i:s');
        $this->db->updatecontent($this->tablename,array('attempt'=>$this->attempt,'last_login'=>$this->last_login),array("username"=>$this->username));
        return $AppConfig->max_nb_attempt - $this->attempt;
    }

    /**
     * Check if the provided password is correct (TRUE) or not (FALSE)
     *
     * @param $password
     * @return bool
     */
    function check_pwd($password) {
        $truepwd = $this->db-> getinfo($this->tablename,"password",array("username"),array("'$this->username'"));
        $check = validate_password($password, $truepwd);
        if ($check == 1) {
            $this->attempt = 0;
            $this->last_login = date('Y-m-d H:i:s');
            $this->db->updatecontent($this->tablename,array('attempt'=>$this->attempt,'last_login'=>$this->last_login),array("username"=>$this->username));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Encrypt the password before adding it to the database
     *
     * @param $password
     * @return string
     */
    function crypt_pwd($password) {
        $hash = create_hash($password);
        return $hash;
    }

}

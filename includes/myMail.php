<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 27/09/14
 * Time: 08:51
 */

class myMail {
    public $mail_from = "";
    public $mail_from_name = "";
    public $mail_host = "";
    public $mail_port = "25";
    public $SMTPDebug = 0;
    public $mail_username = "";
    public $mail_password = "";
    public $SMTP_secure = "none";
    public $pre_header = "[Journal Club]";
    public $site_url = "";

    function __construct() {
        self::get_config();
    }

    public function get_config() {
        require_once($_SESSION['path_to_app'].'/includes/db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $sql = "select variable,value from $config_table";
        $req = $db_set->send_query($sql);
        $class_vars = get_class_vars("myMail");
        while ($row = mysqli_fetch_assoc($req)) {
            $varname = $row['variable'];
            $value = $row["value"];
            if (array_key_exists($varname,$class_vars)) {
                $this->$varname = $value;
            }
        }
        return true;
    }

    public function update_config($post) {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $bdd = $db_set->bdd_connect();
        $class_vars = get_class_vars("site_config");
        foreach ($class_vars as $name=>$value) {
            if (array_key_exists($name,$post)) {
                $value = mysqli_real_escape_string($bdd,$post["$name"]);
            } else {
                $value = mysqli_real_escape_string($bdd,$this->$name);
            }
            $exist = $db_set->getinfo($config_table,"variable",array("variable"),array("'$name'"));
            if (!empty($exist)) {
                $db_set->updatecontent($config_table,"value","'$value'",array("variable"),array("'$name'"));
            } else {
                $db_set->addcontent($config_table,"variable,value","'$name','$value'");
            }
        }

        self::get_config();
        return true;
    }

    function send_verification_mail($hash,$user_mail,$username) {
        require_once($_SESSION['path_to_includes'].'site_config.php');
        require_once($_SESSION['path_to_includes'].'users.php');
        $config = new site_config();
        $admins = $config->getadmin('admin');
        $to = array();
        for ($i=0; $i<count($admins); $i++) {
            $to[] = $admins[$i]['email'];
        }

        $subject = 'Signup | Verification'; // Give the email a subject
        $authorize_url = $this->site_url."index.php?page=verify&email=$user_mail&hash=$hash&result=true";
        $deny_url = $this->site_url."index.php?page=verify&email=$user_mail&hash=$hash&result=false";

        $content = "

        Hello,<br><br>

        <p><strong>$username</strong> wants to create an account.</p>

        <p><a href='$authorize_url'>Authorize</a><br>
        or<br>
        <a href='$deny_url'>Deny</a></p>
        <br>
        The Journal Club Team

        ";

        $body = $this -> formatmail($content);

        return $this->send_mail($to,$subject,$body);
    }

    function send_confirmation_mail($to,$username) {
        require_once($_SESSION['path_to_includes'].'site_config.php');
        require_once($_SESSION['path_to_includes'].'users.php');
        $user = new users();
        $user->getuserinfo($username);

        $subject = 'Signup | Confirmation'; // Give the email a subject
        $login_url = $this->site_url."index.php?page=login";

        $content = "

        Hello $user->fullname,<br><br>
        Thanks for signing up!<br>
        <p>Your account has been created, you can now <a href='$login_url'>log in</a> with the following credentials.</p>

        <p>------------------------<br>
        <strong>Username</strong>: $username<br>
        <strong>Password</strong>: Only you know it!<br>
        ------------------------</p>

        The Journal Club Team

        ";

        $body = $this -> formatmail($content);

        return $this->send_mail($to,$subject,$body);
    }

    function get_mailinglist($type=null) {
        require_once($_SESSION['path_to_app'].'/includes/db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");
        $db_set = new DB_set();
        $sql = "select username,email from $users_table where active=1";
        if (null!=$type) {
            $sql .= " and $type=1";
        }

        $req = $db_set->send_query($sql);
        $mailing_list = array();
        while ($data = mysqli_fetch_array($req)) {
            $cur_mail = $data['email'];
            $mailing_list[] = $cur_mail;
        }
        return $mailing_list;
    }

    function send_to_mailinglist($subject,$body,$type=null,$attachment = NULL) {
        $to = $this->get_mailinglist($type);
        if ($this->send_mail($to,$subject,$body,$attachment)) {
            return true;
        } else {
            return false;
        }
    }

    function send_mail($to,$subject,$body,$attachment = NULL) {
        require_once($_SESSION['path_to_app'].'/libs/PHPMailer-master/class.phpmailer.php');
        require_once($_SESSION['path_to_app'].'/libs/PHPMailer-master/class.smtp.php');
        require_once($_SESSION['path_to_app'].'/includes/db_connect.php');
        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $mail = new PHPMailer();

        $mail->IsSMTP();                                      // set mailer to use SMTP
        $mail->SMTPDebug  = $this->SMTPDebug;                     // enables SMTP debug information (for testing)

        $mail->Mailer = "smtp";
        $mail->Host = $this->mail_host;
        $mail->Port = $this->mail_port;

        if ($this->SMTP_secure != "none") {
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->SMTPSecure = $this->SMTP_secure; // secure transfer enabled REQUIRED for GMail
            $mail->Username = $this->mail_username;
            $mail->Password = $this->mail_password;
        }

        $mail->From = $this->mail_from;
        $mail->FromName = $this->mail_from_name;

        $mail->AddAddress("undisclosed-recipients:;");
        $mail->AddReplyTo($this->mail_from, $this->mail_from_name);

        if (is_array($to)) {
            foreach($to as $to_add){
                $mail->AddBCC($to_add);                  // name is optional
            }
        } else {
            $mail->AddBCC($to);                  // name is optional
        }

        $mail->WordWrap = 50;                                 // set word wrap to 50 characters
        $mail->IsHTML(true);

        $mail->Subject = $this->pre_header." ".$subject;
        $mail->Body    = $body;
        $mail->AltBody="To view the message, please use an HTML compatible email viewer!";

        if($attachment != null){
            if (!$mail->AddAttachment($attachment)) {
                return false;
            }
        }

        if ($rep = $mail->Send()) {
            $mail->ClearAddresses();
            $mail->ClearAttachments();
            return true;
        } else {
            return false;
        }

    }

    function advertise_mail() {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require_once($_SESSION['path_to_includes'].'myMail.php');
        require_once($_SESSION['path_to_includes'].'posts.php');
        require_once($_SESSION['path_to_includes']."presclass.php");
        require_once($_SESSION['path_to_includes']."site_config.php");

        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        $db_set->bdd_connect();
        $config = new site_config('get');

        // Get recent news
        $last_news = new posts();
        $last_news->getlastnews();
        $today = date('Y-m-d');
        if ( date('Y-m-d',strtotime($last_news->date)) < date('Y-m-d',strtotime("$today - 7 days"))) {
            $last_news->content = "No recent news this week";
        }

        // Get future presentations
        $future_session = new presclass();
        $pres_list = $future_session->get_futuresession(4,'mail');

        // Get wishlist
        $wish = new presclass();
        $wish_list = $wish->getwishlist(4,true);

        // Get next session
        $nextpub = new presclass();
        if ($nextpub->get_nextpresentation()) {

            $next_session = "
                <p>The next session of our journal club will be held on the <strong>$nextpub->date</strong> from $config->jc_time_from to $config->jc_time_to in room $config->room.</p>
                <div style='margin: auto; width: 95%; padding: 5px; background-color: rgba(127,127,127,.5);'>
                    <strong>Title:</strong> $nextpub->title <strong>Authors:</strong> $nextpub->authors<br>
                    <strong>Presented by:</strong> $nextpub->orator<br>
                </div>

                <div style='margin: auto;border-color: rgba(127,127,127,.4);width: 95%;padding: 5px;background-color: rgba(127,127,127,.2);'>
                    <strong>Abstract:</strong> $nextpub->summary<br>
                </div>";

            if ($nextpub->link != "") {
                $jc_link = $config->site_url."uploads/".$nextpub->link; // Link to file
                $next_session .= "
                <div style='margin: auto; width: 95%; padding: 5px; background-color: rgba(127,127,127,.5);'>
                <a href='$jc_link' style='color: #CF5151; text-decoration: none;' target='_blank'>Link</a>
                </div>";
            }

        } else {
            $next_session = "<p>Nothing planned for the moment.</p>";
        }

        $content['body'] = "

                <div style='width: 95%; margin: auto;'>
                    <p>Hello,</p>
                    <p>This is your Journal Club weekly digest.</p>
                </div>

                <div style='width: 95%; margin: auto;'>
                    <div style='background-color: #CF5151; width: 100%; color: #eeeeee; padding: 5px; text-align: left; font-weight: bold; font-size: 16px; border-bottom: 2px solid #CF5151; margin-top: 2px;'>
                        Last News
                    </div>

                    <div style='font-size: 14px; width: 100%; padding: 5px; background-color: rgba(127,127,127,.1);'>
                        $last_news->content
                    </div>
                </div>

                <div style='width: 95%; margin: auto;'>
                    <div style='background-color: #CF5151; width: 100%; color: #eeeeee; padding: 5px; text-align: left; font-weight: bold; font-size: 16px; border-bottom: 2px solid #CF5151; margin-top: 2px;'>
                        Upcoming session
                    </div>
                    <div style='font-size: 14px; width: 100%; padding: 5px; background-color: rgba(127,127,127,.1);'>
                        $next_session
                    </div>
                </div>

                <div style='width: 95%; margin: auto;'>
                    <div style='background-color: #CF5151; width: 100%; color: #eeeeee; padding: 5px; text-align: left; font-weight: bold; font-size: 16px; border-bottom: 2px solid #CF5151; margin-top: 2px;'>
                    Future sessions
                    </div>

                    <div style='font-size: 14px; width: 100%; padding: 5px; background-color: rgba(127,127,127,.1);'>
                        $pres_list
                    </div>
                </div>

                <div style='width: 95%; margin: auto;'>
                    <div style='background-color: #CF5151; width: 100%; color: #eeeeee; padding: 5px; text-align: left; font-weight: bold; font-size: 16px; border-bottom: 2px solid #CF5151; margin-top: 2px;'>
                    Wish list
                    </div>

                    <div style='font-size: 14px; width: 100%; padding: 5px; background-color: rgba(127,127,127,.1);'>
                     $wish_list
                    </div>
                </div>

                <div style='width: 95%; margin: auto;'>
                    <p>Cheers,<br>
                    The Journal Club Team</p>
                </div>

        ";

        $content['subject'] = "Last News - ".date('d M Y');

        return $content;
    }

    function reminder_Mail() {
        require_once($_SESSION['path_to_includes'].'db_connect.php');
        require_once($_SESSION['path_to_includes'].'myMail.php');
        require_once($_SESSION['path_to_includes'].'posts.php');
        require_once($_SESSION['path_to_includes']."presclass.php");
        require_once($_SESSION['path_to_includes']."site_config.php");

        require($_SESSION['path_to_app']."/admin/conf/config.php");

        $db_set = new DB_set();
        $db_set->bdd_connect();
        $config = new site_config();
        $config->get_config();
        $nextpub = new presclass();
        $nextpub->get_nextpresentation();

        $jc_link = $config->site_url."uploads/".$nextpub->link; // Link to file

        $content['body'] = "
            <div style='width: 95%; margin: auto;'>
                <p>Hello,<br>
                This is a reminder for the next Journal Club session.</p>
            </div>

            <div style='width: 95%; margin: auto;'>
                <div style='background-color: #CF5151; width: 100%; color: #eeeeee; padding: 5px; text-align: left; font-weight: bold; font-size: 16px; border-bottom: 2px solid #CF5151; margin-top: 2px;'>
                    Next session
                </div>
                <div style='font-size: 14px; width: 100%; padding: 5px; background-color: rgba(127,127,127,.1);'>
                    <p>The next session of our journal club will be held on the <strong>$nextpub->date</strong> from $config->jc_time_from to $config->jc_time_to in room $config->room.</p>
                    <div style='margin: auto; width: 95%; padding: 5px; background-color: rgba(127,127,127,.5);'>
                        <strong>Title:</strong> $nextpub->title | <strong>Authors:</strong> $nextpub->authors<br>
                        <strong>Presented by:</strong> $nextpub->orator<br>
                    </div>

                    <div style='margin: auto;border-color: rgba(127,127,127,.4);width: 95%;padding: 5px;background-color: rgba(127,127,127,.2);'>
                        <strong>Abstract:</strong> $nextpub->summary<br>
                    </div>

                    <div style='margin: auto; width: 95%; padding: 5px; background-color: rgba(127,127,127,.5);'>
                    <a href='$jc_link' style='color: #CF5151; text-decoration: none;' target='_blank'>Link</a>
                    </div>
                </div>
            </div>

            <div style='width: 95%; margin: auto;'>
                <p>Cheers,<br>
                The Journal Club Team</p>
            </div>
        ";

        $content['subject'] = "Next session: ".$nextpub->date." -reminder";

        return $content;
    }

    function formatmail($content) {
        $profile_url = $this->site_url.'index.php?page=profile';
        $body = "
            <div style='font-family: Helvetica Neue, Helvetica, Arial, sans-serif sans-serif; color: #000000; font-weight: 300; font-size: 15px; width: 80%; margin: auto;'>
                <div style='line-height: 1.2; width: 100%; color: #000000;'>
                    <div style='font-size: 30px; color: #cccccc; height: 40px; text-align: center; background-color: #555555;'>Journal Club</div>

                    <!-- Upcoming event -->
                    <div style='padding: 10px; margin: auto; text-align: justify; background-color: #dddddd;'>$content</div>

                    <!-- Footer section -->
                    <div style='color: #EEEEEE; width: 100%; height: 30px; text-align: center; background-color: #555555;'>
                        This email has been sent automatically. You can choose to no longer receive notification emails from your
                        <a href='$profile_url' style='color: #CF5151; text-decoration: none;' target='_blank' >profile</a> page.
                    </div>
                </div>

            </div>";

        return $body;
    }

} 
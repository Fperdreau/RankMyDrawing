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

session_start();
// Includes required files (classes)
include_once($_SESSION['path_to_includes'].'includes.php');

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Contact form
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['contact_send'])) {
    $mail = new myMail();
    $config = new site_config('get');
    $usr_msg = htmlspecialchars($_POST["message"]);
    $usr_mail = htmlspecialchars($_POST["mail"]);
    $usr_name = htmlspecialchars($_POST["name"]);
    $content = "Message sent by $usr_name ($usr_mail):<br><p>$usr_msg</p>";
    $body = $mail -> formatmail($content);
    $subject = "Contact from $usr_name";

    if ($mail->send_mail($config->mail_from,$subject,$body)) {
        $result = "sent";
    } else {
        $result = "not_sent";
    }
    echo json_encode($result);
}

// Add user to the database
if(!empty($_POST['add_user'])) {
    $user = new participant();
    $refid = $_POST['refid'];
    $result = $user->make($_POST,$refid);
    $_SESSION['userid'] = $result;
    $_SESSION['refid'] = $refid;
    echo json_encode($result);
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Experiment
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['startexp'])) {
    $userid = $_POST['userid'];
    $refid = $_POST['refid'];
    $user = new participant($userid,$refid);
    $ref = new DrawRef($user->refid);
    $exp = new Experiment($ref,$userid);
    $exp->genlist();
    $_SESSION['pairslist'] = $exp->pairslist;

    foreach ($exp->pairslist as $trial=>$items) {
        $pair1[] = $items[0];
        $pair2[] = $items[1];
    }
    $user->pair1 = implode(',',$pair1);
    $user->pair2 = implode(',',$pair2);
    $user->time_start = time();
    $user->update();
    echo json_encode(true);
}

if (!empty($_POST['endtrial'])) {
    $userid = $_POST['userid'];
    $refid = $_POST['refid'];
    $winnerid = $_POST['winner'];
    $loserid = $_POST['loser'];

    $ref = new DrawRef($refid);
    $user = new participant($userid,$refid);
    $exp = new Experiment($ref,$user->userid);
    $winner = new ELO($refid,$winnerid);
    $loser = new ELO($refid,$loserid);

    // Update ELO scores
    $new_scores = $exp->updateELO($winnerid,$loserid);
    $winner->score = $new_scores[$winnerid];
    $loser->score = $new_scores[$loserid];
    $winner->updateresults($loserid,1);
    $loser->updateresults($winnerid,0);

    // Add user responses to the database
    $user->response1 = $user->response1.",$winnerid";
    $user->response2 = $user->response2.",$loserid";
    $user->update();

    // Get new trial parameters
    $nexttrial = $exp->gettrial();

    if ($nexttrial <= $exp->ntrials) {
        $pairslist = $_SESSION['pairslist'];
        $trialinfo = $pairslist[$nexttrial];
        $item1id = $trialinfo[0];
        $item2id = $trialinfo[1];
        $item1 = new ELO($refid,$item1id);
        $item2 = new ELO($refid,$item2id);
        $img1 = "images/$ref->file_id/img/$item1->filename";
        $img2 = "images/$ref->file_id/img/$item2->filename";

        $result['item1'] = $item1id;
        $result['item2'] = $item2id;
        $result['img1'] = $img1;
        $result['img2'] = $img2;
        $result['trial'] = $nexttrial;
        $result['progress'] = $nexttrial/$exp->ntrials;
        $result['stopexp'] = false;
    } else {
        $config = new site_config('get');
        $result['stopexp'] = true;
        $result['redirecturl'] = $config->redirecturl;
    }


    echo json_encode($result);
}

if (!empty($_POST['endexp'])) {
    $post['time_end'] = time();
    $user->update($post);
    echo json_encode(true);
}

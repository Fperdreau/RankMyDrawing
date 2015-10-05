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

// Includes required files (classes)
require('../includes/boot.php');

if (!empty($_POST['get_app_status'])) {
    echo json_encode($AppConfig->status);
    exit;
}

if (!empty($_POST['setTimer'])) {
    $start = $_POST['start'] === 'true';
    $refid = (!empty($_SESSION['refid'])) ? $_SESSION['refid']:null;
    $ref = new DrawRef($db, $refid);
    $maxTime = $ref->maxtime;

    $start = ($maxTime > 0 || !empty($_SESSION['stopTime']));

    if ($start) {
        $result['start'] = true;
        $result['maxtime'] = $maxTime;
    } else {
        $result['start'] = false;
    }

    echo json_encode($result);
    exit;
}

if (!empty($_POST['getUrl'])) {
    echo json_encode($AppConfig->redirecturl);
    exit;
}
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Contact form
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['contact_send'])) {
    $usr_msg = htmlspecialchars($_POST["message"]);
    $usr_mail = htmlspecialchars($_POST["mail"]);
    $usr_name = htmlspecialchars($_POST["name"]);
    $content = "Message sent by $usr_name ($usr_mail):<br><p>$usr_msg</p>";
    $body = $AppMail -> formatmail($content);
    $subject = "Contact from $usr_name";

    if ($AppMail->send_mail($AppConfig->mail_from,$subject,$body)) {
        $result['status'] = true;
        $result['msg'] = "Thank you for your message";
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
}

// Add user to the database
if(!empty($_POST['add_user'])) {
    $user = new Participant($db);
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
    $user = new Participant($db,$userid,$refid);
    $ref = new DrawRef($db,$user->refid);
    $exp = new Experiment($db,$ref,$userid);
    $exp->genlist();
    $_SESSION['pairslist'] = $exp->pairslist;

    $pair1 = array();
    $pair2 = array();
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
    $userid = htmlspecialchars($_POST['userid']);
    $refid = htmlspecialchars($_POST['refid']);
    $winnerid = htmlspecialchars($_POST['winner']);
    $loserid = htmlspecialchars($_POST['loser']);

    $ref = new DrawRef($db,$refid);
    $user = new Participant($db,$userid,$refid);
    $exp = new Experiment($db,$ref,$user->userid);
    $winner = new Ranking($db,$refid,$winnerid);
    $loser = new Ranking($db,$refid,$loserid);

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
        $item1 = new Ranking($db,$refid,$item1id);
        $item2 = new Ranking($db,$refid,$item2id);

        // Get corresponding files
        $file1 = new Uploads($db,$item1->filename);
        $file2 = new Uploads($db,$item2->filename);

        $img1 = "images/$ref->file_id/img/$file1->filename";
        $img2 = "images/$ref->file_id/img/$file2->filename";

        $result['item1'] = $item1id;
        $result['item2'] = $item2id;
        $result['img1'] = $img1;
        $result['img2'] = $img2;
        $result['trial'] = $nexttrial;
        $result['progress'] = $nexttrial/$exp->ntrials;
        $result['stopexp'] = false;
    } else {
        $result['stopexp'] = true;
        $result['redirecturl'] = $AppConfig->redirecturl;
    }
    echo json_encode($result);
}

if (!empty($_POST['endexp'])) {
    $post['time_end'] = time();
    $user->update($post);
    echo json_encode(true);
}

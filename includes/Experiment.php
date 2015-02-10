<?php
require_once($_SESSION['path_to_includes'].'includes.php');

class Experiment {

    public $trial = 0;
    public $ntrials = 0;
    public $refid = "";
    public $userid = "";
    public $pair = 0;
    public $pairslist = array();
    public $maxcomp = 0;

    // Constructors
    function __construct($ref,$userid) {
        $this->ntrials = $ref->nb_pairs;
        $this->refid = $ref->file_id;
        $this->userid = $userid;
    }

    // Generate list of pairs
    public function genlist() {

        $ref = new DrawRef($this->refid);

        // Get max value of the table
        $this->maxcomp = self::getmaxcomp($ref->drawlist);

        // Get all possible combinations
        $pairslist = array();
        $old = array('id','file_id');
        foreach ($ref->drawlist as $item) {
            $storedata = self::getrowdata($item);
            foreach ($storedata as $opp=>$comp) {
                if ($opp != $item && !in_array($opp,$old)) {
                    $pairslist["$item,$opp"] = $comp;
                    $old[] = $item;
                }
            }
        }

        $nb_pairs = count($pairslist);

        // Sort list of pairs by nb of comparisons
        asort($pairslist);

        // Take the required number of pairs defined by ntrials
        $pairslist = array_slice($pairslist, 0, $this->ntrials);

        // Randomize the list order
        $pairs = array_keys($pairslist);
        shuffle($pairs);
        foreach($pairs as $pair) {
            $shuffledlist[$pair] = $pairslist[$pair];
        }

        // Make final list
        $trial = 1;
        foreach ($shuffledlist as $pair=>$comp) {
            $items = explode(',',$pair);
            $list[$trial] = $items;
            $trial++;
        }
        $this->pairslist = $list;
    }

    // Get maximum number of comparisons
    function getmaxcomp($drawlist) {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->refid."_comp_mat";

        $max_values = array();
        foreach ($drawlist as $item) {
            $sql = "SELECT MAX($item) FROM $reftable";
            $req = $db_set->send_query($sql);
            $data = mysqli_fetch_array($req);
            $max_values[] = $data[0];
        }
        return max($max_values);
    }

    // Get row data
    public function getrowdata($item) {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->refid."_comp_mat";
        $sql = "SELECT * FROM $reftable WHERE file_id='$item'";
        $req = $db_set->send_query($sql);
        return mysqli_fetch_assoc($req);
    }

    // Get trial parameters
    public function gettrial() {
        $db_set = new DB_set();
        $reftable = $db_set->dbprefix.$this->refid."_users";
        $sql = "SELECT response1 FROM $reftable WHERE userid='$this->userid'";
        $req = $db_set->send_query($sql);
        $params = mysqli_fetch_assoc($req);
        $trial = count(explode(',',$params['response1'])) + 1;
        return $trial;
    }

    // Update ELO scores
    public function updateELO($winnerid,$loserid) {
        $outcomes = array(1,0);
        $itemsid = array($winnerid,$loserid);
        $scores = array();
        for ($i=0;$i<=1;$i++) {
            if ($i == 0) {
                $ind1 = 0;
                $ind2 = 1;
            } else {
                $ind1 = 1;
                $ind2 = 0;
            }
            $item = new ELO($this->refid,$itemsid[$ind1]);
            $opp = new ELO($this->refid,$itemsid[$ind2]);
            $W = $outcomes[$ind1]; // Match's outcome (0=lose, 1=win)
            $diff = $item->score - $opp->score; // Difference of scores
            $pwin = 1/(1 + (pow(10, -$diff/400))); // Win probability
            $coef = 800/(2*($item->nb_occ+1)); // Adjust coefficient to the item's level
            $scores[$item->file_id] = $item->score + $coef * ($W - $pwin); // Compute new ELO score
        }
        return $scores;
    }
}

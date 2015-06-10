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

class Experiment {

    private $db;
    public $trial = 0;
    public $ntrials = 0;
    public $refid = "";
    public $userid = "";
    public $pair = 0;
    public $pairslist = array();
    public $maxcomp = 0;

    /**
     * Constructor
     * @param AppDb $db
     * @param $ref
     * @param $userid
     */
    function __construct(AppDb $db, $ref, $userid) {
        $this->db = $db;
        $this->ntrials = $ref->nb_pairs;
        $this->refid = $ref->file_id;
        $this->userid = $userid;
    }

    /**
     * Generate list of pairs
     */
    public function genlist() {

        $ref = new DrawRef($this->db,$this->refid);

        // Get max value of the table
        $this->maxcomp = self::getmaxcomp($ref->drawlist);

        // Get all possible combinations
        $pairslist = array();
        $old = array('id','file_id');
        foreach ($ref->drawlist as $item) {
            $storedata = self::getrowdata($item);
            foreach ($storedata as $opp=>$comp) {
                if ($opp != $item && !in_array($opp,$old)) {
                    $pairslist["$item,$opp"] = (int)$comp;
                    $old[] = $item;
                }
            }
        }

        // Sort list of pairs by nb of comparisons
        asort($pairslist);

        // Take the required number of pairs defined by ntrials
        $pairslist = array_slice($pairslist, 0, $this->ntrials);

        // Randomize the list order
        $pairs = array_keys($pairslist);
        shuffle($pairs);
        $shuffledlist = array();
        foreach($pairs as $pair) {
            $shuffledlist[$pair] = $pairslist[$pair];
        }

        // Make final list
        $trial = 1;
        $list = array();
        foreach ($shuffledlist as $pair=>$comp) {
            $items = explode(',',$pair);
            $list[$trial] = $items;
            $trial++;
        }
        $this->pairslist = $list;
    }

    /**
     * Get maximum number of comparisons
     * @param $drawlist
     * @return mixed
     */
    function getmaxcomp($drawlist) {
        $reftable = $this->db->dbprefix.'_'.$this->refid."_comp_mat";

        $max_values = array();
        foreach ($drawlist as $item) {
            $sql = "SELECT MAX($item) FROM $reftable";
            $req = $this->db->send_query($sql);
            $data = mysqli_fetch_array($req);
            $max_values[] = $data[0];
        }
        return max($max_values);
    }

    /**
     * Get AppTable row data
     * @param $item
     * @return array|null
     */
    public function getrowdata($item) {
        $reftable = $this->db->dbprefix.'_'.$this->refid."_comp_mat";
        $sql = "SELECT * FROM $reftable WHERE file_id='$item'";
        $req = $this->db->send_query($sql);
        return mysqli_fetch_assoc($req);
    }

    /**
     * Get trial parameters
     * @return int
     */
    public function gettrial() {
        $reftable = $this->db->dbprefix.'_'.$this->refid."_users";
        $sql = "SELECT response1 FROM $reftable WHERE userid='$this->userid'";
        $req = $this->db->send_query($sql);
        $params = mysqli_fetch_assoc($req);
        $trial = count(explode(',',$params['response1'])) + 1;
        return $trial;
    }

    /**
     * Update ELO scores
     * @param $winnerid
     * @param $loserid
     * @return array
     */
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
            $item = new Ranking($this->db,$this->refid,$itemsid[$ind1]);
            $opp = new Ranking($this->db,$this->refid,$itemsid[$ind2]);
            $W = $outcomes[$ind1]; // Match's outcome (0=lose, 1=win)
            $diff = $item->score - $opp->score; // Difference of scores
            $pwin = 1/(1 + (pow(10, -$diff/400))); // Win probability
            $coef = 800/(2*($item->nb_occ+1)); // Adjust coefficient to the item's level
            $scores[$item->file_id] = $item->score + $coef * ($W - $pwin); // Compute new ELO score
        }
        return $scores;
    }
}

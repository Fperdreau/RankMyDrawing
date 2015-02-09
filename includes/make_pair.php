<?php
	
	class make_pair {
					
		public function genlist($npair,$drawref) {
			require_once($_SESSION['path_to_app'].'includes/db_connect.php');
            require_once($_SESSION['path_to_app'].'admin/includes/factorial.php');

			$db_set = new DB_set();
			$bdd = $db_set -> bdd_connect();
			$listFiles = $db_set -> getinfo($drawref.'_ranking','file_name');
			$nfile = count($listFiles); // number of files
            $maxpairs = factorial($nfile)/(factorial($nfile-2)*factorial(2));
			if ($nfile > 1 && $npair <= $maxpairs) {

				// Look for the less comparated elements
				$old_pairs1 = array();
				$old_pairs2 = array();

				$ready = 0;
				$redo = 0;
				$iredo = 0;
				$nb_items = 0;
				$cc = 1;
				while ($ready == 0) {

					// Get max value of the table
					if ($redo == 0) {
						$max_values = array();
						for ($a=1;$a<=$nfile;$a++) {
							$storedata = $this -> getrowdata($a,$drawref);
							$max_values[$a-1] = max($storedata);
						}

						$maxval = max($max_values);
						if ($maxval == 0) {
							$maxval = 1;
						}
					}

					// Check if there is enough elements to compare
					for ($a=1;$a<=$nfile;$a++) {
						$storedata = $this->getrowdata($a,$drawref);

						$d = 1;
						foreach ($storedata as $dat) {
							if ($dat < $maxval && $a!=$d) {
								$ok = 1;
								for ($ccc=0;$ccc<=count($old_pairs1);$ccc++){
									if ( ($old_pairs1[$ccc] == $a and $old_pairs2[$ccc] == $d) or ($old_pairs1[$ccc] == $d and $old_pairs2[$ccc] == $a)) {
										$ok = 0;
										break;
									}
								}

								if ($ok == 1) {
									$old_pairs1[$cc-1] = $a;
									$old_pairs2[$cc-1] = $d;
									$nb_items++;
									$cc++;
								}
							}
							$d++;
						}
					}

					if ($nb_items < $npair) {
						$redo = 1;
						$iredo = $nb_items;
						$maxval++;
						// echo "<p> Not enough items. Nbitems: $nb_items Maxvalue:$maxval iredo:$iredo<p>";
					} else {
						// echo "<p> We found enough pairs of images:".$nb_items." </p>";
						// echo "<p> Maxvalue:".$maxval." iredo: ".$iredo."<p>";
						$ready = 1;
					}
				}

				$pair = $this->make_list($npair,$old_pairs1,$old_pairs2,$iredo);
				return $pair;
			} else {
                if ($nfile <= 1) {
                    echo "<p class='warning'>Reference drawings and their corresponding drawings
				    must be uploaded so that the experiment can be started!</p>";
                }

                if ($npair <= $maxpairs) {
                    echo "<p class='warning'>Wrong experimental settings. The required number of comparisons per participant exceed the maximum number of comparisons</p>";
                }

                exit;
			}
		}
		
		// Generate combinatory list of npairs
		public function make_list($npair,$old_pairs1,$old_pairs2,$iredo) {	
		
			$poscomp = count($old_pairs1);
			
			// randomly select pairs
			$p = $iredo + 1;
			$ids = range(0,$iredo);
			
			$pair1 = array_slice($old_pairs1,0,$iredo);
			$pair2 = array_slice($old_pairs2,0,$iredo);
			
			$init_time = time();
			$cur_time = time();
			$cpt = $p;
			while ($p<$npair) {
			
				$cur_time = time();
				if ( ($cur_time >= ($init_time + 5))) {
					print_r("<p> number of possible pairs: ".$poscomp."</p>");
					print_r("<p> Found pairs: ".$p."</p>");
					for ($cp=0;$cp<=$p;$cp++) {
						print_r("<p>Pair $cp: ".$pair1[$cp]." & ".$pair2[$cp]."</p>");		
					}
					die("<p> Selection took too long! </p>");
				}
				
				$id = rand($iredo,$poscomp);
				
				// check if not already present
				if (!in_array($id,$ids)) {
						
					// If not already present, we add it to the list
					$new_pair1 = $old_pairs1[$id];
					$new_pair2 = $old_pairs2[$id];
					
					$ok = 1;
					for ($ii=0;$ii<=count($pair1);$ii++) {
						if ( ($new_pair1 == $pair1[$ii] and $new_pair2 == $pair2[$ii]) or ($new_pair1 == $pair2[$ii] and $new_pair2 == $pair1[$ii]) ) {
							$ok = 0;
							//print_r("<p> Pair:".$p." Img: (".$new_pair1." & ".$new_pair2.") already present!</p>");
							break;
						}
					}
					
					if ($ok == 1) {
						$ids[$cpt] = $id;
						$pair1[$p-1] = $new_pair1;
						$pair2[$p-1] = $new_pair2;
						$p++;	
						$cpt++;
					}
				}
			}
			// make pairs list
/* 			$newids = array_merge($default_ids,$ids);
			$ids = $newids; */
			//echo "<p> length ids: ".count($ids)."</p>";

			//echo "<br/><pre> Contenu ids"; print_r($ids); echo "</pre>";

			$pair1 = array();
			$pair2 = array();
			for ($p=0;$p<$npair;$p++) {
				$ind = $ids[$p];
				$pair1[$p] = $old_pairs1[$ind];
				$pair2[$p] = $old_pairs2[$ind];
			}
			
			$pair = array(
				array($pair1),
				array($pair2));
				
			return $pair;
		}
		
		// Get row data and replace NULL values by 0
		public function getrowdata($a,$drawref) {
			require_once($_SESSION['path_to_app'].'includes/db_connect.php');
			$db_set = new DB_set();
			$bdd = $db_set -> bdd_connect();
			
			$img_col = "Img_".$a;
			$sql = "SELECT '".$img_col."' FROM ".$drawref."_comp_mat";
			$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error()); 
			
			$storedata = array();
			$r = 0;
			while ($row = mysql_fetch_assoc($req)) {
				$dat = $row[$img_col];
				if (is_null($dat)) {
					$dat = 0;
				}
				$storedata[$r] = $dat;
				$r++;
			}
				
			if (empty($storedata)) {
				exit("Storedata is empty");
			}
			return $storedata;
		}
	}
?>
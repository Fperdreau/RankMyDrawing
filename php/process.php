<?php		
	session_start();

	require_once($_SESSION['path_to_app'].'includes/ELO_class.php');
	require_once($_SESSION['path_to_app'].'includes/user.php');
	require_once($_SESSION['path_to_app'].'includes/db_connect.php');
	require_once($_SESSION['path_to_app'].'admin/conf/config.php');
	
	$id = $_SESSION['id'];
	$current_pair = $_SESSION['pair'];
	$drawref = $_SESSION['drawref'];
	
	if ($_POST) {
		$ok = 0;
		if (isset($_POST['response1']) || isset($_POST['response1_x'])) {
			$response = 1;
		} elseif (isset($_POST['response2']) || isset($_POST['response2_x'])) {
			$response = 2;
		} else {
			die('<p>Sorry, unexpected answer. Not your fault!</p>');
		}
			
		if ($response == 1) {
			$result[0] = 1;
			$result[1] = 0;
			$ok = 1;
		} elseif ($response == 2) {
			$result[0] = 0;
			$result[1] = 1;
			$ok = 1;
		} else {
			$ok = 0; 
			die("<p> ERROR: No response! </p>");
		}
		
		if ($ok==1) {
			$_SESSION['response1'][$current_pair-1] = $result[0];
			$_SESSION['response2'][$current_pair-1] = $result[1];
			$ind1 = $_SESSION['pairs'][0][0][$current_pair-1];
			$ind2 = $_SESSION['pairs'][1][0][$current_pair-1];
			
			$pairId = array($ind1,$ind2);
			$rank = new ELO_rank();
			$rank -> compute_ELO_rank($db_prefix.$drawref,$pairId,$result);
			
			// Save results
			$response1 = implode(",",$_SESSION['response1']);
			$response2 = implode(",",$_SESSION['response2']);
			$pair1 = implode(",",$_SESSION['pairs'][0][0]);
			$pair2 = implode(",",$_SESSION['pairs'][1][0]);
			$user = new User();
			
			$user->write_sql($db_prefix.$drawref,$id,'response1',$response1);
			$user->write_sql($db_prefix.$drawref,$id,'response2',$response2);
			$user->write_sql($db_prefix.$drawref,$id,'pair1',$pair1);
			$user->write_sql($db_prefix.$drawref,$id,'pair2',$pair2);
			
			$current_pair++;
			$_SESSION['pair'] = $current_pair;
		}
		
		// Redirect after processing
		$www = $_SESSION['path_to_pages']."pages/drawexp.php";
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$www.'");
		// -->
		</script>';
	} else {
		die("<p> ERROR: no POST variable </p>");
	}
	
?>
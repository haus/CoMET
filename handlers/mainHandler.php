<?php
/*
		CoMET is a stand-alone member equity tracking application designed to integrate with IS4C and Fannie.
	    Copyright (C) 2009  Matthaus Litteken
		
		This file is part of CoMET.
		
	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.

	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.

	    You should have received a copy of the GNU General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');
	require_once('../includes/mysqli_connect.php');
	require_once('../includes/functions.php');

	// Initializing some variables.
	$details = false;
	$owner = false;
	$owners = false;
	if (!isset($_POST['value']))
		echo '{ ';

	// Process the data, update as needed.
	// If the new data is different from the current data, insert a new row into the appropriate table, update the old end date to today/now,
	// make the new start date today/now and the new end date null.
	// If there isn't a matching row, insert it into the appropriate table, set the start date to now/today and the end date to null.

	if (isset($_POST['changed']) && $_POST['changed'] != 'false') {
		$_POST['address'] = trim($_POST['address']);
		$_POST['city'] = trim($_POST['city']);
		$_POST['phone'] = trim($_POST['phone']);
		$_POST['zip'] = trim($_POST['zip']);
		$_POST['email'] = trim($_POST['email']);

		for ($i = 1; $i <= $_SESSION['houseHoldSize']; $i++) {
			$_POST['first'][$i] = trim($_POST['first'][$i]);
			$_POST['last'][$i] = trim($_POST['last'][$i]);
		}
	
		if (empty($_POST['address']) && empty($_POST['city']) && empty($_POST['phone']) && empty($_POST['zip'])) {
			// All blank, if 
			// if it's a new record, skip insert, just move along.
			$details = false;
		} else {
			$details = true;
		}

		if ( empty($_POST['first'][1]) && empty($_POST['last'][1]) ) { // First person is mandatory.
			$owner = false;
		} else {
			$owner = true;
		}

		// Then check each owner row.
		for ($i = 1; $i <= $_SESSION['houseHoldSize']; $i++) {
			if ( !empty($_POST['first'][$i]) || !empty($_POST['last'][$i]) ) {
				$owners = true;
			}
		}


		// First check the details row. 
		// Look for any entries with the current cardNo. If none, insert. If they are there, check for differences between the two.
		// If they are the same, do nothing. If they are different...
		//	- update the old row to have an end date of now/today
		//	- insert a new row with new info and a start date of now today with an end date of null
		$dCheckQ = "SELECT * FROM details WHERE cardNo={$_SESSION['cardNo']}";
		$dCheckR = mysqli_query($DBS['comet'], $dCheckQ);

		if ($dCheckR) $numRows = mysqli_num_rows($dCheckR);
		else printf("<p>Query: %s</p><p>MySQLi Error: %s</p>\n", $dCheckQ, mysqli_error($DBS['comet']));

		if ($numRows == 0) { // Easy case. Check for data, if it's there, insert a row.
			if (!$details && !$owners) { // Nothing to write.
				echo ' "message": "' . $_SESSION['userID'] . '",';	
			} elseif ($details && $owner) { // Something to write, check for bad secondary owner rows
				checkPost(); // Will kill the script if there are errors.
				echo ' "message": "data written", ';
				$phone = ereg_replace("[^0-9]", "", escape_data($DBS['comet'], $_POST['phone']));
				$zip = ereg_replace("[^0-9]", "", escape_data($DBS['comet'], $_POST['zip']));
				
				// Join Date Validation
				$joinMonth = (isset($_POST['joinDate']) ? (int) substr($_POST['joinDate'], 5, 2) : 0);
				$joinDay = (isset($_POST['joinDate']) ? (int) substr($_POST['joinDate'], 8, 2) : 0);
				$joinYear = (isset($_POST['joinDate']) ? (int) substr($_POST['joinDate'], 0, 4) : 0);
				$joinDate = (checkdate($joinMonth, $joinDay, $joinYear) ? "$joinYear-$joinMonth-$joinDay" : date('Y-m-d'));
				
				// Share price validation
				$sharePrice = ((isset($_POST['sharePrice']) && is_numeric($_POST['sharePrice']) && $_POST['sharePrice'] >= 0) ? 
					(int) $_POST['sharePrice'] : $_SESSION['sharePrice']);
				
				// Plan validation
				$plan = ((isset($_POST['plan']) && is_numeric($_POST['plan']) && $_POST['plan'] > 0) ? (int) $_POST['plan'] : 1);
				
				// Details then owners.
				$detailsQ = sprintf(
					"INSERT INTO raw_details VALUES 
						(%u, '%s', '%s', '%s', '%s', %u, '%s', NULL, %u, '%s', %s, curdate(), NULL, '%s', NULL)", 
						$_SESSION['cardNo'], 
						escape_data($DBS['comet'], $_POST['address']),
						$phone,
						escape_data($DBS['comet'], $_POST['city']),
						escape_data($DBS['comet'], $_POST['state']),
						$zip,
						escape_data($DBS['comet'], $_POST['email']),
						$plan,
						$joinDate,
						$sharePrice,
						$_SESSION['userID']
					);
				$detailsR = mysqli_query($DBS['comet'], $detailsQ);
				
				for ($i = 1; $i <= $_SESSION['houseHoldSize']; $i++) {
					if ( !empty($_POST['first'][$i]) && !empty($_POST['last'][$i]) ) {
						$ownerQ = sprintf(
							"INSERT INTO raw_owners VALUES 
							(%u, %u, '%s', '%s', %u, %u, %u, %u, %u, curdate(), NULL, %u, NULL)", 
								$_SESSION['cardNo'], 
								$i,
								escape_data($DBS['comet'], $_POST['first'][$i]),
								escape_data($DBS['comet'], $_POST['last'][$i]),
								escape_data($DBS['comet'], $_POST['discount'][$i]),
								escape_data($DBS['comet'], $_POST['memType'][$i]),
								escape_data($DBS['comet'], $_POST['staff'][$i]),
								(isset($_POST['charge'][$i]) && $_POST['charge'][$i] == 'on' ? 1 : 0),
								(isset($_POST['checks'][$i]) && $_POST['checks'][$i] == 'on' ? 1 : 0),
								$_SESSION['userID']
						);
						$ownerR = mysqli_query($DBS['comet'], $ownerQ);
					}
				}
				echo ' "message": "data written", ';
			} else { // Partially filled in. Error.
				echo ' "errorMsg": "Partially Filled In." }';
				exit();
			}
		} elseif ($numRows == 1) { // Already existing row. Update or not.
			if (!$details && !$owners) { // Empty. Error out.
				echo ' "errorMsg": "Record cannot be empty." }';
				exit();
			} elseif ($details && $owner) { // Mostly filled in. Check secondary owner rows.
				checkPost(); // Will kill the script if there are errors.
				$phone = ereg_replace("[^0-9]", "", escape_data($DBS['comet'], $_POST['phone']));
				$zip = ereg_replace("[^0-9]", "", escape_data($DBS['comet'], $_POST['zip']));
			
				$detailsQ = sprintf( // If this returns a record, there have been no changes.
					"SELECT * FROM details WHERE cardNo=%u AND address='%s' AND phone='%s' AND city='%s' AND state='%s' AND zip=%u AND email='%s'", 
						$_SESSION['cardNo'], 
						escape_data($DBS['comet'], $_POST['address']),
						$phone,
						escape_data($DBS['comet'], $_POST['city']),
						escape_data($DBS['comet'], $_POST['state']),
						$zip,
						escape_data($DBS['comet'], $_POST['email'])
					);
				$detailsR = mysqli_query($DBS['comet'], $detailsQ);
			
				if (mysqli_num_rows($detailsR) == 1) echo ' "message": "No changes", ';
				else {
					// Updating records. Two queries. One to update the old record, one to insert the new record.
					$detailsUpdateQ = sprintf(
						"UPDATE raw_details SET endDate=curdate() WHERE cardNo=%u AND endDate IS NULL",
						$_SESSION['cardNo']
						);
					$detailsUpdateR = mysqli_query($DBS['comet'], $detailsUpdateQ);
					if ($detailsUpdateR) {
						$detailsInsertQ = sprintf(
							"INSERT INTO raw_details VALUES 
								(%u, '%s', '%s', '%s', '%s', %u, '%s', NULL, 1, curdate(), %s, curdate(), NULL, '%s', NULL)", 
								$_SESSION['cardNo'], 
								escape_data($DBS['comet'], $_POST['address']),
								$phone,
								escape_data($DBS['comet'], $_POST['city']),
								escape_data($DBS['comet'], $_POST['state']),
								$zip,
								escape_data($DBS['comet'], $_POST['email']),
								$_SESSION['sharePrice'],
								$_SESSION['userID']
							);
						$detailsInsertR = mysqli_query($DBS['comet'], $detailsInsertQ);
					}
				
					if ($detailsInsertR)
						echo ' "message": "Changes made, details updated", ';
				
				}
			
				for ($i = 1; $i <= $_SESSION['houseHoldSize']; $i++) {
					/* Four possibilities. 
						- No changes (numRows1 = 1 and numRows = 1)
						- Someone being added who wasn't there before (numRows1 = 0, first and last not empty)
						- Someone being taken away that was there before (numRows1 = 1, numRows = 0, first and last empty)
						- Someone being updated (numRows1 = 1, numRows = 0, first and last not empty)
					*/
					$ownerQ1 = sprintf("SELECT * FROM owners WHERE cardNo=%u AND personNum=%u", $_SESSION['cardNo'], $i);
					$ownerR1 = mysqli_query($DBS['comet'], $ownerQ1);
					$ownerNumRows1 = mysqli_num_rows($ownerR1);

					$first = escape_data($DBS['comet'], $_POST['first'][$i]);
					$last = escape_data($DBS['comet'], $_POST['last'][$i]);
				
					$ownerQ = sprintf("SELECT * FROM owners WHERE 
						cardNo=%u AND 
						firstName='%s' AND 
						lastName='%s' AND 
						personNum=%u AND 
						discount=%u AND 
						memType=%u AND 
						staff=%u AND 
						chargeOk=%u AND 
						writeChecks=%u",
						$_SESSION['cardNo'],
						$first,
						$last,
						$i,
						escape_data($DBS['comet'], $_POST['discount'][$i]),
						escape_data($DBS['comet'], $_POST['memType'][$i]),
						escape_data($DBS['comet'], $_POST['staff'][$i]),
						(isset($_POST['charge'][$i]) && $_POST['charge'][$i] == 'on' ? 1 : 0),
						(isset($_POST['checks'][$i]) && $_POST['checks'][$i] == 'on' ? 1 : 0)
					);
				
					$ownerR = mysqli_query($DBS['comet'], $ownerQ);
					$ownerNumRows = mysqli_num_rows($ownerR);
				
					if ($ownerNumRows1 == 0 && !empty($first) && !empty($last)) { // Adding person to card
						$ownerInsertQ = sprintf("INSERT INTO raw_owners VALUES
							(%u, %u, '%s', '%s', %u, %u, %u, %u, %u, curdate(), NULL, %u, NULL)",
							$_SESSION['cardNo'],
							$i,
							$first,
							$last,
							escape_data($DBS['comet'], $_POST['discount'][$i]),
							escape_data($DBS['comet'], $_POST['memType'][$i]),
							escape_data($DBS['comet'], $_POST['staff'][$i]),
							(isset($_POST['charge'][$i]) && $_POST['charge'][$i] == 'on' ? 1 : 0),
							(isset($_POST['checks'][$i]) && $_POST['checks'][$i] == 'on' ? 1 : 0),
							$_SESSION['userID']
						);
						$ownerInsertR = mysqli_query($DBS['comet'], $ownerInsertQ);
					
						if ($ownerInsertR)
							echo ' "message": "success ' . $i . ' added ", ';
						else
							echo ' "errorMsg": "error ' . $i . ' error ", '; 
					} elseif ($ownerNumRows1 == 1 && $ownerNumRows == 0 && empty($first) && empty($last)) { // Removing person from card
						$ownerUpdateQ = sprintf("UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL",
							$_SESSION['cardNo'],
							$i
						);
						$ownerUpdateR = mysqli_query($DBS['comet'], $ownerUpdateQ);
					
						if ($ownerUpdateR)
							echo ' "message": "success ' . $i . ' removed", ';
						else
							echo ' "errorMsg": "failure ' . $i . ' removed", ';
					} elseif ($ownerNumRows1 == 1 && $ownerNumRows == 0 && !empty($first) && !empty($last)) { // Updating person on card
						// First update the old row.
						$ownerUpdateQ = sprintf("UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL",
							$_SESSION['cardNo'],
							$i
						);
						$ownerUpdateR = mysqli_query($DBS['comet'], $ownerUpdateQ);
					
						if ($ownerUpdateR) {
							// Then insert the new row.
							echo ' "message": "success ' . $i . ' updated", ';
						
							$ownerInsertQ = sprintf("INSERT INTO raw_owners VALUES
								(%u, %u, '%s', '%s', %u, %u, %u, %u, %u, curdate(), NULL, %u, NULL)",
								$_SESSION['cardNo'],
								$i,
								$first,
								$last,
								escape_data($DBS['comet'], $_POST['discount'][$i]),
								escape_data($DBS['comet'], $_POST['memType'][$i]),
								escape_data($DBS['comet'], $_POST['staff'][$i]),
								(isset($_POST['charge'][$i]) && $_POST['charge'][$i] == 'on' ? 1 : 0),
								(isset($_POST['checks'][$i]) && $_POST['checks'][$i] == 'on' ? 1 : 0),
								$_SESSION['userID']
							);
							$ownerInsertR = mysqli_query($DBS['comet'], $ownerInsertQ);
						
							if ($ownerInsertR)
								echo ' "message": "success ' . $i . ' inserted", ';
							else
								echo ' "message": "failure on ' . $i . ' inserted", ';
							
						}
					}
				
				}
			
			
			
			} else { // Partially filled in. Error.
				echo ' "errorMsg": "Partially Filled In" }';
				exit();
			}
		} else {
			echo ' "message": "More than one record. Database error.", ';
		}

	}
	
	if (isset($_POST['firstSearch']) && !empty($_POST['firstSearch'])) {
		$_REQUEST['navButton'] = 'search';
		$search = explode(' ', escape_data($DBS['comet'], $_POST['firstSearch']));
		$count = count($search);
		$_POST['value'] = trim($search[$count-1], '[');
		$_POST['value'] = trim($_POST['value'], ']');
	} elseif (isset($_POST['lastSearch']) && !empty($_POST['lastSearch'])) {
		$_REQUEST['navButton'] = 'search';
		$search = explode(' ', escape_data($DBS['comet'], $_POST['lastSearch']));
		$count = count($search);
		$_POST['value'] = trim($search[$count-1], '[');
		$_POST['value'] = trim($_POST['value'], ']');
	}

	// Read the submit type, adjust the $_SESSION['cardNo'] and let the main.php JS handle updating the divs
	$navButton = (isset($_REQUEST['navButton']) ? escape_data($DBS['comet'], $_REQUEST['navButton']) : NULL);
	//echo '"errorMsg": "' . $navButton . '", ';
	switch ($navButton) {
		case 'search':
			if (is_numeric($_POST['value']) && $_POST['value'] > 0)
				$_SESSION['cardNo'] = (int) $_POST['value'];

			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;
				
		case 'customRecord':
			if (is_numeric($_POST['value']) && $_POST['value'] > 0)
				$_SESSION['cardNo'] = (int) $_POST['value'];

			echo $_SESSION['cardNo'];
		break;
	
		case 'nextRecord':
			$cardQ = "SELECT cardNo FROM owners WHERE cardNo > {$_SESSION['cardNo']} ORDER BY cardNo ASC LIMIT 1";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
			if (mysqli_num_rows($cardR) == 1)
				list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;

		case 'prevRecord':
			$cardQ = "SELECT cardNo FROM owners WHERE cardNo < {$_SESSION['cardNo']} ORDER BY cardNo DESC LIMIT 1";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
			if (mysqli_num_rows($cardR) == 1)
				list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;

		case 'firstRecord':
			$cardQ = "SELECT MIN(cardNo) FROM owners";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;

		case 'lastRecord':
			$cardQ = "SELECT MAX(cardNo) FROM owners";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;
	
		case 'new':
			$cardQ = "SELECT MAX(cardNo)+1 FROM owners WHERE cardNo NOT IN (9999, 99999)";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
		
			if (mysqli_num_rows($cardR) == 1)
				list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			else // Brand new installation case.
				$_SESSION['cardNo'] = 1;
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;
		
		case 'current':
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;
		
		default:
			$cardQ = "SELECT MAX(cardNo)+1 FROM owners";
			$cardR = mysqli_query($DBS['comet'], $cardQ);
		
			if (mysqli_num_rows($cardR) == 1)
				list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			else // Brand new installation case.
				$_SESSION['cardNo'] = 1;
			echo ' "cardNo": "' . $_SESSION['cardNo'] . '" }';
		break;
		
	}

} else {
	header('Location: ../index.php');
}

function checkPost() {
	for ($i = 2; $i <= $_SESSION['houseHoldSize']; $i++) {
		if ( empty($_POST['first'][$i]) XOR empty($_POST['last'][$i]) ) {
			echo ' "message": "Partially filled in. Exiting in error." } ';
			exit();
		}
	}
}
?>
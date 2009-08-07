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
require_once('../includes/config.php');
require_once('../includes/mysqli_connect.php');
require_once('../includes/functions.php');

// Initializing some variables.
$details = false;
$owner = false;
$owners = false;
echo '{ ';

// Process the data, update as needed.
// If the new data is different from the current data, insert a new row into the appropriate table, update the old end date to today/now,
// make the new start date today/now and the new end date null.
// If there isn't a matching row, insert it into the appropriate table, set the start date to now/today and the end date to null.
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

if ($_POST['changed'] != 'false') {

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
			for ($i = 2; $i <= $_SESSION['houseHoldSize']; $i++) {
				if ( empty($_POST['first'][$i]) XOR empty($_POST['last'][$i]) ) {
					echo ' "message": "Partially filled in. Exiting in error." } ';
					exit();
				}
			}
			echo ' "message": "data written" ';
			$address = (strpos($_POST['address'], '\n') ? explode('\n', $_POST['address']) : $_POST['address']);
			
			// Details then owners.
			$detailsQ = sprintf(
				"INSERT INTO raw_details VALUES 
					(%u, '%s', %s, '%s', '%s', '%s', %u, '%s', NULL, 0, curdate(), %s, curdate(), NULL, '%s', NULL)", 
					$_SESSION['cardNo'], 
					escape_data($DBS['comet'], (is_array($address) ? $address[0] : $address)),
					escape_data($DBS['comet'], (is_array($address) ? "'" . $address[1] . "'" : 'NULL')),
					escape_data($DBS['comet'], $_POST['phone']),
					escape_data($DBS['comet'], $_POST['city']),
					escape_data($DBS['comet'], $_POST['state']),
					escape_data($DBS['comet'], $_POST['zip']),
					escape_data($DBS['comet'], $_POST['email']),
					$_SESSION['sharePrice'],
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
							(isset($_POST['checks'][$i]) && $_POST['checks'][$i] == 'on' ? 1 : 0),
							(isset($_POST['charge'][$i]) && $_POST['charge'][$i] == 'on' ? 1 : 0),
							$_SESSION['userID']
					);
					$ownerR = mysqli_query($DBS['comet'], $ownerQ);
				}
			}
			echo ' "message": "data written", ';
		} else { // Partially filled in. Error.
			echo ' "message": "Partially Filled In." } ';
			exit();
		}
	} elseif ($numRows == 1) { // Already existing row. Update or not.
		if (!$details && !$owners) { // Empty. Error out.
			echo ' "message": "Record cannot be empty."} ';
			exit();
		} elseif ($details && $owner) { // Mostly filled in. Check secondary owner rows.
			
		} else { // Partially filled in. Error.
			echo ' "message": "Partially Filled In" }';
			exit();
		}
	} else {
		echo ' "message": "More than one record. Database error.", ';
	}

}

// Read the submit type, adjust the $_SESSION['cardNo'] and let the main.php JS handle updating the divs
switch ($_POST['navButton']) {
	case 'nextRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo > {$_SESSION['cardNo']} ORDER BY cardNo ASC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'prevRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo < {$_SESSION['cardNo']} ORDER BY cardNo DESC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'firstRecord':
		$cardQ = "SELECT MIN(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'lastRecord':
		$cardQ = "SELECT MAX(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;
	
	case 'new':
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;
	
	default:
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo ' "cardNo": "' . $_SESSION['cardNo'] . '"}';
	break;
		
}
?>
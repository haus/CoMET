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

// Initializing some variables.
$details = true;
$owner = true;

// Process the data, update as needed.
// If the new data is different from the current data, insert a new row into the appropriate table, update the old end date to today/now,
// make the new start date today/now and the new end date null.
// If there isn't a matching row, insert it into the appropriate table, set the start date to now/today and the end date to null.
if (empty($_POST['address']) && empty($_POST['city']) && empty($_POST['phone']) && empty($_POST['zip'])) {
	// All blank, if 
	// if it's a new record, skip insert, just move along.
	$details = false;
}

if ( empty($_POST['first'][1]) && empty($_POST['last'][1]) ) { // First person is mandatory.
	$owner = false;
}

// Then check each owner row.
for ($i = 1; $i <= $_SESSION['houseHoldSize']; $i++) {
	if ( !empty($_POST['first'][$i]) || !empty($_POST['last'][$i]) ) {
		$owner = true;
	}
	
}

echo '{ first: "' . $_POST['first'][1] . '"},';

// First check the details row. 
// Look for any entries with the current cardNo. If none, insert. If they are there, check for differences between the two.
// If they are the same, do nothing. If they are different...
//	- update the old row to have an end date of now/today
//	- insert a new row with new info and a start date of now today with an end date of null
$dCheckQ = "SELECT * FROM details WHERE cardNo={$_SESSION['cardNo']}";
$dCheckR = mysqli_query($DBS['comet'], $dCheckQ);

if ($dCheckR) $numRows = mysqli_num_rows($dCheckR);
else printf("<p>Query: %s</p><p>MySQLi Error: %s</p>\n", $dCheckQ, mysqli_error($DBS['comet']));

if ($numRows == 0 && $details && $owner) { // Easy case. Check for data, if it's there, insert a row.
	echo '{ message: "error" },';
} elseif ($numRows == 1 && $details && $owner) { // Already existing record...has it changed?
	//something
} else {
	//something
}

// Read the submit type, adjust the $_SESSION['cardNo'] and let the main.php JS handle updating the divs
switch ($_POST['navButton']) {
	case 'nextRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo > {$_SESSION['cardNo']} ORDER BY cardNo ASC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'prevRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo < {$_SESSION['cardNo']} ORDER BY cardNo DESC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'firstRecord':
		$cardQ = "SELECT MIN(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;

	case 'lastRecord':
		$cardQ = "SELECT MAX(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;
	
	case 'new':
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;
	
	default:
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo '{ cardNo: "' . $_SESSION['cardNo'] . '"}';
	break;
		
}
?>
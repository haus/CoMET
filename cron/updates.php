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
$baseDir = (substr(__DIR__, -1) == '/' ? substr(__DIR__, 0, -1) : __DIR__);
$baseDir = substr(__DIR__, 0, strrpos($baseDir, '/'));

require_once($baseDir . '/includes/config.php');
require_once($baseDir . '/includes/functions.php');

$_SESSION['userID'] = 0; // System user...
$_SESSION['level'] = 5; // Max level...

// First people on hold...owners who will be put on hold...
// Move to memtype of 5. Update discount, check for staff of 1 or 5.
$onHoldQ = sprintf("SELECT d.cardNo, o.personNum, o.staff
	FROM owners AS o 
		INNER JOIN details AS d ON o.cardNo = d.cardNo 
	WHERE TIMESTAMPDIFF(DAY, d.nextPayment, curdate()) BETWEEN 30 AND %u 
		AND o.memType IN (1, 2)", $_SESSION['inactiveDays'] - 1);
$onHoldR = mysqli_query($DBS['comet'], $onHoldQ);

if (!$onHoldR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $onHoldQ);

echo 'On Hold' . "\n";
while (list($cardNo, $personNum, $staff) = mysqli_fetch_row($onHoldR)) {
	$newDisc = ($staff == 1 || $staff == 5 ? 15 : 0);
	
	// First update the latest entry...
	$updateQ = sprintf(
		"UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL", 
		$cardNo, $personNum
		);
	$updateR = mysqli_query($DBS['comet'], $updateQ);
	
	if ($updateR) {
		// Then insert the new entries...
		$insertQ = sprintf(
			"INSERT INTO raw_owners (
			SELECT cardNo, personNum, firstName, lastName, %u, %u, staff, chargeOk, writeChecks, curdate(), NULL, %u, NULL
				FROM raw_owners
				WHERE cardNo=%u AND DATE(endDate) = curdate() AND personNum = %u GROUP BY cardNo, personNum HAVING MAX(endDate))",
				$newDisc, 5, $_SESSION['userID'], $cardNo, $personNum
		);
		$insertR = mysqli_query($DBS['comet'], $insertQ);
		
		if ($insertR)
			echo "Success: $cardNo - $personNum - $staff - $newDisc Updated\n";
		else
			printf("Error: %s, Query: %s\n", mysqli_error($DBS['comet']), $insertQ);
	} else {
		printf("Error: %s, Query: %s\n", mysqli_error($DBS['comet']), $updateQ);
	}
}

// Then inactives...owners who will be made inactive...
// Move to memtype of 3. Update discount, check for staff of 1 or 5.
$inactiveQ = sprintf("SELECT d.cardNo, o.personNum, o.staff
	FROM owners AS o 
		INNER JOIN details AS d ON o.cardNo = d.cardNo
	WHERE TIMESTAMPDIFF(DAY, d.nextPayment, curdate()) >= %u
		AND o.memType IN (1, 2, 5)", $_SESSION['inactiveDays']);
$inactiveR = mysqli_query($DBS['comet'], $inactiveQ);

if (!$inactiveR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $inactiveQ);

echo 'Inactive' . "\n";	
while (list($cardNo, $personNum, $staff) = mysqli_fetch_row($inactiveR)) {
	$newDisc = ($staff == 1 || $staff == 5 ? 15 : 0);
	
	// First update the latest entry...
	$updateQ = sprintf(
		"UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL", 
		$cardNo, $personNum
		);
	$updateR = mysqli_query($DBS['comet'], $updateQ);
	
	if ($updateR) {
		// Then insert the new entries...
		$insertQ = sprintf(
			"INSERT INTO raw_owners (
			SELECT cardNo, personNum, firstName, lastName, %u, %u, staff, chargeOk, writeChecks, curdate(), NULL, %u, NULL
				FROM raw_owners
				WHERE cardNo=%u AND DATE(endDate) = curdate() AND personNum = %u GROUP BY cardNo, personNum HAVING MAX(endDate))",
				$newDisc, 3, $_SESSION['userID'], $cardNo, $personNum
		);
		$insertR = mysqli_query($DBS['comet'], $insertQ);
		
		if ($insertR)
			echo "Success: $cardNo - $personNum - $staff - $newDisc Updated\n";
		else
			printf("Error: %s, Query: %s\n", mysqli_error($DBS['comet']), $insertQ);
	} else {
		printf("Error: %s, Query: %s\n", mysqli_error($DBS['comet']), $updateQ);
	}
}

?>
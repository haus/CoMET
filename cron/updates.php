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

/*
	// First update the old row.
	$ownerUpdateQ = sprintf("UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL",
		$_SESSION['cardNo'],
		$personNum
	);
	$ownerUpdateR = mysqli_query($DBS['comet'], $ownerUpdateQ);

	if ($ownerUpdateR) {
		// Then insert the new row.
		
		$ownerInsertQ = sprintf("INSERT INTO raw_owners (
			SELECT cardNo, personNum, firstName, lastName, %u, %u, staff, chargeOk, writeChecks, curdate(), NULL, %u, NULL
				FROM raw_owners
				WHERE cardNo=%u AND DATE(endDate) = curdate() AND personNum = %u GROUP BY cardNo, personNum HAVING MAX(endDate))",
				$newDisc, $newMemType, $_SESSION['userID'], $_SESSION['cardNo'], $personNum
		);
		$ownerInsertR = mysqli_query($DBS['comet'], $ownerInsertQ);

		if ($ownerInsertR) {
			
		} else {
			echo '{ "errorMsg": "failure on ' . $personNum . ' inserted" }';
		}
	}
*/

// First people on hold...owners who will be put on hold...
// Move to memtype of 5. Update discount, check for staff of 1 or 5.
$onHoldQ = sprintf("SELECT d.cardNo, COUNT(personNum)
	FROM owners AS o 
		INNER JOIN details AS d ON o.cardNo = d.cardNo 
	WHERE TIMESTAMPDIFF(DAY, d.nextPayment, curdate()) BETWEEN 30 AND %u 
		AND o.memType = 2
	GROUP BY d.cardNo", $_SESSION['inactiveDays'] - 1);
$onHoldR = mysqli_query($DBS['comet'], $onHoldQ);

if (!$onHoldR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $onHoldQ);

echo 'On Hold' . "\n";
while (list($cardNo, $count) = mysqli_fetch_row($onHoldR)) {
	// First update the latest entry...
	$updateQ = "";
	
	// Then insert the new entries...
	$insertQ = "";
	echo "$cardNo - $count\n";
}

// Then inactives...owners who will be made inactive...
// Move to memtype of 3. Update discount, check for staff of 1 or 5.
$inactiveQ = sprintf("SELECT d.cardNo, COUNT(personNum)
	FROM owners AS o 
		INNER JOIN details AS d ON o.cardNo = d.cardNo
	WHERE TIMESTAMPDIFF(DAY, d.nextPayment, curdate()) >= %u
		AND o.memType IN (2,5)
	GROUP BY d.cardNo", $_SESSION['inactiveDays']);
$inactiveR = mysqli_query($DBS['comet'], $inactiveQ);

if (!$inactiveR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $inactiveQ);

echo 'Inactive' . "\n";	
while (list($cardNo, $count) = mysqli_fetch_row($inactiveR)) {
	$updateQ = "";
	
	echo "$cardNo - $count\n";
}
?>
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

// First people on hold...owners who will be put on hold...
// Move to memtype of 5.
$onHoldQ = "SELECT d.cardNo
	FROM owners AS o 
		INNER JOIN details AS d ON o.cardNo = d.cardNo 
	WHERE TIMESTAMPDIFF(DAY, d.nextPayment, curdate()) BETWEEN 30 and 269 
		AND o.memType = 2
	GROUP BY d.cardNo";
$onHoldR = mysqli_query($DBS['comet'], $onHoldQ);

if (!$onHoldR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $onHoldQ);


// Then inactives...owners who will be made inactive...
// Move to memtype of 3
$inactiveQ = sprintf(
	'SELECT d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, \'%%M %%e, %%Y\'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (2,5)
		AND o.personNum = 1
		AND d.email IS NOT NULL AND d.email <> \'\'
	GROUP BY cardNo 
		HAVING diff >= %u', $_SESSION['inactiveDays']);
$inactiveR = mysqli_query($DBS['comet'], $inactiveQ);

$inactiveMsg = $_SESSION['inactiveMsg'];
$inactiveSubject = $_SESSION['inactiveSubject'];

if (!$inactiveR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $inactiveQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($inactiveR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	
}
?>
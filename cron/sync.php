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

/**
 * This cron script pushes updates from the updates.php script to fannie.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

$baseDir = (substr(__DIR__, -1) == '/' ? substr(__DIR__, 0, -1) : __DIR__);
$baseDir = substr(__DIR__, 0, strrpos($baseDir, '/'));

require_once($baseDir . '/includes/config.php');
require_once($baseDir . '/includes/functions.php');
	
// Initialize $body variable...
$body = '';
$count = 0;

// Records to be added...
$newQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk FROM owners
	WHERE CONCAT(cardNo, '-', personNum) NOT IN
		(SELECT CONCAT(cardNo, '-', personNum) FROM {$_SESSION['opDB']}.custdata)";
$newR = mysqli_query($DBS['comet'], $newQ);

if (!$newR) printf("Query: %s, Error %s\n", $newQ, mysqli_error($DBS['comet']));

if (mysqli_num_rows($newR) > 0) $newList = "Records added:\n";

while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge) = mysqli_fetch_row($newR)) {
	$newList .= sprintf("Card #: %u, Person #%u, Name: %s %s ",
		$cardNo, $personNum, $first, $last);
	
	// Insert logic goes here...
	$insertQ = sprintf("INSERT INTO custdata 
			(
				CardNo, personNum, LastName, FirstName, CashBack, Balance, Discount, MemDiscountLimit, 
				ChargeOk, WriteChecks, StoreCoupons, Type, memType, staff, SSI, Purchases, NumberOfChecks, 
				memCoupons, blueLine, Shown, id, modified, phone, CouponOK
			) 
			VALUES 
			(%u, %u, '%s', '%s', 60, 0, %u, %u, %u, %u, 1, '%s', %u, %u, 0, 0, 0, 1, NULL, 1, NULL, NOW(), NULL, 1)",
			(int)$cardNo, (int)$personNum, escapeData($DBS['comet'], $last), escapeData($DBS['comet'], $first), (int)$discount, ($charge == 1 ? 9999 : 0), $charge, $check, 
			($memType == 0 || $memType == 6 || $memType == 7 ? 'reg' : 'pc'), $memType, $staff
		);
	$insertR = mysqli_query($DBS['is4c_op'], $insertQ);
	
	if (!$insertR) printf("Query: %s, Error %s\n", $insertQ, mysqli_error($DBS['is4c_op']));
	
	$newList .= sprintf("%s\n", ($insertR) ? 'inserted successfully' : 'insert failure');
	
	$count++;
}

// Records to be deleted...mail details to contact info...
$goneQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk, SSI 
	FROM {$_SESSION['opDB']}.custdata
	WHERE CONCAT(cardNo, '-', personNum) NOT IN
		(SELECT CONCAT(cardNo, '-', personNum) FROM owners)";
$goneR = mysqli_query($DBS['comet'], $goneQ);

if (!$goneR) printf("Query: %s, Error %s\n", $goneQ, mysqli_error($DBS['comet']));

if (mysqli_num_rows($goneR) > 0) $delList = "\nRecords deleted:\n";

while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge, $ssi) = mysqli_fetch_row($goneR)) {
	$delList .= sprintf("Card #: %u, Person #%u, Name: %s %s ",
		$cardNo, $personNum, $first, $last);
	
	$deleteQ = sprintf("DELETE FROM custdata
		WHERE cardNo=%u AND personNum=%u
		LIMIT 1", (int)$cardNo, (int)$personNum);
	$deleteR = mysqli_query($DBS['is4c_op'], $deleteQ);
	
	if (!$deleteR) printf("Query: %s, Error %s\n", $deleteQ, mysqli_error($DBS['is4c_op']));
	
	$delList .= sprintf("%s\n", ($deleteR) ? 'deleted successfully' : 'delete failure');
	
	$body .= sprintf("Deleted: Card #: %u, Person #%u, First: %s, Last: %s, Discount: %u, Staff: %u, Memtype: %u, Check: %u, Charge: %u, Hours: %u\n",
		$cardNo, $personNum, $first, $last, $discount, $staff, $memType, $check, $charge, $ssi);
	
	$count++;
}

// Records to be updated...
$updateListQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk FROM owners
	WHERE CONCAT_WS(',', cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk) NOT IN 
		(SELECT CONCAT_WS(',', cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk) 
		FROM {$_SESSION['opDB']}.custdata)";
$updateListR = mysqli_query($DBS['comet'], $updateListQ);

if (!$updateListR) printf("Query: %s, Error %s\n", $updateListQ, mysqli_error($DBS['comet']));

if (mysqli_num_rows($updateListR) > 0) $upList = "Records updated:\n";
while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge) = mysqli_fetch_row($updateListR)) {
	$upList .= sprintf("Card #: %u, Person #%u, Name: %s %s ",
		$cardNo, $personNum, $first, $last);
	
	$updateQ = sprintf("UPDATE custdata
		SET firstname='%s', lastname='%s', discount=%u, memtype=%u, staff=%u, writechecks=%u, chargeok=%u, memdiscountlimit=%u, type='%s'
		WHERE cardNo=%u AND personNum=%u", 
			escapeData($DBS['comet'], $first), escapeData($DBS['comet'], $last), (int)$discount, (int)$memType, (int)$staff, (int)$check, (int)$charge, 
			($charge == 1 ? 9999 : 0), ($memType == 0 || $memType == 6 || $memType == 7 ? 'reg' : 'pc'), $cardNo, $personNum);
	$updateR = mysqli_query($DBS['is4c_op'], $updateQ);
	
	if (!$updateR) printf("Query: %s, Error %s\n", $updateR, mysqli_error($DBS['is4c_op']));
	
	$upList .= sprintf("%s\n", ($updateR) ? 'updated successfully' : 'update failure');
	
	$count++;
}

// Mail admin...
$from = "CoMET <comet@albertagrocery.coop>";
$to = sprintf('"%s" <%s>', $_SESSION['reminderFrom'], $_SESSION['reminderEmail']);
$subject = "CoMET Mail - Deleted Records";

// Force sync of fannie to lanes using cURL...
$curlSync = curl_init($_SESSION['syncURL']);
curl_setopt($curlSync, CURLOPT_RETURNTRANSFER, true);
if (curl_exec($curlSync) !== false) {
	echo "CoMET synched to Fannie. Fannie synched to lanes.\n
		Results:\n";
} else {
	echo "CoMET synched to Fannie. Fannie not synched to lanes.
		cURL Error: " . curl_error($curlSync) . "\n";
}

if (isset($newList))
	echo $newList;
if (isset($delList))
 	echo $delList;
if (isset($upList))
	echo $upList;

if (!empty($body)) cometMail(array('to' => $to, 'from' => $from, 'subject' => $subject, 'body' => $body, 'system'));

if ($count == 0) echo "No changes to push to Fannie.\n";
else echo "$count records updated.\n";


?>
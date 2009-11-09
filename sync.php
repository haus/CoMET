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

?>
<script type="text/javascript">
	$(document).ready(function() {
		$('body').css('cursor', 'default');
	}); 
</script>
<?php

if (isset($_SESSION['level'])) {
	require_once('./includes/config.php');
	require_once('./includes/mysqli_connect.php');
	
	// Initialize $body variable...
	$body = '';
	
	// Records to be added...
	$newQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk FROM owners
		WHERE CONCAT(cardNo, '-', personNum) NOT IN
			(SELECT CONCAT(cardNo, '-', personNum) FROM {$_SESSION['is4c_op']['database']}.custdata)";
	$newR = mysqli_query($DBS['comet'], $newQ);
	
	if (!$newR) printf('<h3>Query: %s<br />Error %s</h3>', $newQ, mysqli_error($DBS['comet']));
	
	if (mysqli_num_rows($newR) > 0) echo '<h3>Records added:</h3>';
	
	while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge) = mysqli_fetch_row($newR)) {
		printf("Card #: %u, Person #: %u, Name: %s %s ",
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
				$cardNo, $personNum, $last, $first, $discount, ($charge == 1 ? 9999 : 0), $charge, $check, 
				($memType == 0 || $memType == 6 || $memType == 7 ? 'reg' : 'pc'), $memType, $staff
			);
		$insertR = mysqli_query($DBS['is4c_op'], $insertQ);
		
		if (!$insertR) printf('<h3>Query: %s<br />Error %s</h3>', $insertQ, mysqli_error($DBS['is4c_op']));
		
		printf('%s<br />', ($insertR) ? 'inserted successfully' : 'insert failure');
		
	}
	
	// Records to be deleted...mail details to contact info...
	$goneQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk, SSI 
		FROM {$_SESSION['is4c_op']['database']}.custdata
		WHERE CONCAT(cardNo, '-', personNum) NOT IN
			(SELECT CONCAT(cardNo, '-', personNum) FROM owners)";
	$goneR = mysqli_query($DBS['comet'], $goneQ);
	
	if (!$goneR) printf('<h3>Query: %s<br />Error %s</h3>', $goneQ, mysqli_error($DBS['comet']));

	if (mysqli_num_rows($goneR) > 0) echo '<br /><h3>Records added:</h3>';
	
	while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge, $ssi) = mysqli_fetch_row($goneR)) {
		printf("Card #: %u, Person #: %u, Name: %s %s ",
			$cardNo, $personNum, $first, $last);
		
		$deleteQ = sprintf("DELETE FROM custdata
			WHERE cardNo=%u AND personNum=%u
			LIMIT 1", $cardNo, $personNum);
		$deleteR = mysqli_query($DBS['is4c_op'], $deleteQ);
		
		if (!$deleteR) printf('<h3>Query: %s<br />Error %s</h3>', $deleteQ, mysqli_error($DBS['is4c_op']));
		
		printf('%s<br />', ($deleteR) ? 'deleted successfully' : 'delete failure');
		
		$body .= sprintf("Deleted: Card #: %u, Person #: %u, First: %s, Last: %s, Discount: %u, Staff: %u, Memtype: %u, Check: %u, Charge: %u, Hours: %u\n",
			$cardNo, $personNum, $first, $last, $discount, $staff, $memType, $check, $charge, $ssi);
	}
	
	// Records to be updated...
	$updateListQ = "SELECT cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk FROM owners
		WHERE CONCAT_WS(',', cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk) NOT IN 
			(SELECT CONCAT_WS(',', cardNo, personNum, firstName, lastName, discount, memType, staff, writeChecks, chargeOk) 
			FROM {$_SESSION['is4c_op']['database']}.custdata)";
	$updateListR = mysqli_query($DBS['comet'], $updateListQ);
	
	if (!$updateListR) printf('<h3>Query: %s<br />Error %s</h3>', $updateListQ, mysqli_error($DBS['comet']));
	
	if (mysqli_num_rows($updateListR) > 0) echo '<br /><h3>Records updated:</h3>';
	while (list($cardNo, $personNum, $first, $last, $discount, $memType, $staff, $check, $charge) = mysqli_fetch_row($updateListR)) {
		printf("Card #: %u, Person #: %u, Name: %s %s ",
			$cardNo, $personNum, $first, $last);
		
		$updateQ = sprintf("UPDATE custdata
			SET firstname='%s', lastname='%s', discount=%u, memtype=%u, staff=%u, writechecks=%u, chargeok=%u, memdiscountlimit=%u, type='%s'
			WHERE cardNo=%u AND personNum=%u", 
				$first, $last, $discount, $memType, $staff, $check, $charge, 
				($charge == 1 ? 9999 : 0), ($memType == 0 || $memType == 6 || $memType == 7 ? 'reg' : 'pc'), $cardNo, $personNum);
		$updateR = mysqli_query($DBS['is4c_op'], $updateQ);
		
		if (!$updateR) printf('<h3>Query: %s<br />Error %s</h3>', $updateR, mysqli_error($DBS['is4c_op']));
		
		printf('%s<br />', ($updateR) ? 'updated successfully' : 'update failure');
	}
	
	// Mail admin...
	$headers = "From: Me <mlitteken@gmail.com>\r\n";
	$to = "Me <mlitteken@gmail.com>";
	$subject = "CoMET Mail - Deleted Records";
	
	if (!empty($body)) mail($to, $subject, $body, $headers);
	
} else {
	header('Location: ../index.php');
}

?>
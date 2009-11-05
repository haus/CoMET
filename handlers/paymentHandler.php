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

	// Sanitize the data.
	if (isset($_POST['date'])) $date = escape_data($DBS['comet'], $_POST['date']);
	if (isset($_POST['memo'])) $memo = escape_data($DBS['comet'], $_POST['memo']);
	if (isset($_POST['reference'])) $reference = escape_data($DBS['comet'], $_POST['reference']);
	if (isset($_POST['amount'])) $amount = escape_data($DBS['comet'], $_POST['amount']);

	// Validate the data a bit.
	if (isset($_POST['removeID']) && is_numeric($_POST['removeID'])) {
		$paymentQ = sprintf("DELETE FROM payments WHERE paymentID=%u LIMIT 1",
			escape_data($DBS['comet'], $_POST['removeID'])
		);
	
		$paymentR = mysqli_query($DBS['comet'], $paymentQ);
		if (!$paymentR) {
			printf('{ "errorMsg":"Query: %s, Error: %s" }',
				$paymentQ, 
				mysqli_error($DBS['comet'])
			);
		} else {
			echo '{ "success": "success!" }';
		}
	} elseif (!empty($date) && !empty($amount) && is_numeric($amount) && checkdate(substr($date, 0, 2), substr($date, 3, 2), substr($date, 6, 4))) { // Non empty, numeric amount, non empty actual date.
		$year = substr($date, 6, 4);
		$month = substr($date, 0, 2);
		$day = substr($date, 3, 2);
		$date = "$year-$month-$day";
		
		$checkQ = "SELECT SUM(p.amount), MAX(date), d.nextPayment, d.joined, d.sharePrice, d.paymentPlan, pp.frequency, pp.amount 
			FROM payments AS p 
				RIGHT JOIN details AS d ON (d.cardNo = p.cardNo) 
				INNER JOIN paymentPlans AS pp ON (d.paymentPlan = pp.planID)
			WHERE d.cardNo={$_SESSION['cardNo']}";
		$checkR = mysqli_query($DBS['comet'], $checkQ);
		
		list($total, $last, $next, $trash, $sPrice, $pPlan, $pFreq, $pAmount) = mysqli_fetch_row($checkR);
		$total = (is_null($total) ? 0 : $total);
		$sPrice = (is_null($sPrice) ? $_SESSION['sharePrice'] : $sPrice);
		
		if ($amount < 0) {
			echo '{ "errorMsg" : "A non-positive payment is not okay." }';
		} elseif (($sPrice - $total) >= $amount) { // The payment won't overpay the share. Good to go.
			$paymentQ = sprintf("INSERT INTO payments VALUES (NULL, %s, %f, '%s', %s, %u, %u)",
				(empty($memo) ? 'NULL' : "'" . $memo . "'"),
				$amount,
				$date,
				(empty($reference) ? 'NULL' : "'" . $reference . "'"),
				$_SESSION['userID'],
				$_SESSION['cardNo']
			);
	
			$paymentR = mysqli_query($DBS['comet'], $paymentQ);
			if (!$paymentR) {
				printf('{ "errorMsg":"Query: %s, Error: %s" }',
					$dateQ, 
					mysqli_error($DBS['comet'])
				);
			} else { // Update next payment date, member status, etc. Logic goes here.
				// ACG Specific Algorithm: Member status updates.
				if (($total + $amount) == $sPrice) { // If fully paid, shareholder.
					$newMemType = 2;
				} else { // If not fully paid, subscriber
					$newMemType = 1;
				}
					
				$selectQ = "SELECT personNum, memType, staff
					FROM owners
					WHERE cardNo = {$_SESSION['cardNo']}";
				$selectR = mysqli_query($DBS['comet'], $selectQ);

				while (list($personNum, $memType, $staff) = mysqli_fetch_row($selectR)) {
					if ($memType == 1 || $memType == 5) {
						if ($staff == 0 || $staff == 2 || $staff == 3 || $staff == 6) {
							$newDisc = 2;
						} elseif ($staff == 1 || $staff == 4 || $staff == 5) {
							$newDisc = 15;
						}
						
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
					}
				}
				
				// Then update the details.
				// Next Payment Logic
				// If paid up fully, next due = null
				if (($total + $amount) == $sPrice) { // If fully paid, shareholder.
					$nextDue = 'NULL';

				// Okay this part requires PHP 5.3 or greater. Date Time work.
				// If after next due or null, next due = payment date + (12/period)
				} elseif (is_null($next) || (strtotime($date) > strtotime($next))) {
					$period = (int)(12 / $pFreq);
					$nextDue = date_create($date);
					$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
					$nextDue = "'" . date_format($nextDue, 'Y-m-d') . "'";
					/*echo '{ "errorMsg": "Adding to pmtdate. ' . "\n" . 'PmtDate: ' . strtotime($date) . '. ' . "\n" . 'NextDue: ' . strtotime($next) . '." }';
					exit();*/
				// If before or on next due, next due = next due + (12/period)	
				} elseif (strtotime($date) <= strtotime($next)) {
					$period = (int)(12 / $pFreq);
					$nextDue = date_create($next);
					$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
					$nextDue = "'" . date_format($nextDue, 'Y-m-d') . "'";
					/*echo '{ "errorMsg": "Adding to pmtdate. ' . "\n" . 'PmtDate: ' . strtotime($date) . '. ' . "\n" . 'NextDue: ' . strtotime($next) . '." }';
					exit();*/

				}

				$updateQ = "UPDATE raw_details SET endDate=curdate() WHERE cardNo={$_SESSION['cardNo']} AND endDate IS NULL";
				$updateR = mysqli_query($DBS['comet'], $updateQ);
				
				if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
					$insertQ = sprintf("INSERT INTO raw_details (
						SELECT cardNo, address, phone, city, state, zip, email, %s, paymentPlan, joined, sharePrice, curdate(), 
							NULL, %u, NULL 
							FROM raw_details
							WHERE cardNo=%u 
								AND DATE(endDate) = curdate() 
								AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo={$_SESSION['cardNo']}) 
							GROUP BY cardNo HAVING MAX(endDate))", 
							$nextDue, $_SESSION['userID'], $_SESSION['cardNo']);
					$insertR = mysqli_query($DBS['comet'], $insertQ);
				
				if ($insertR)
					echo '{ "success": "success!" }';
				else
					echo '{ "errorMsg":"Query: ' . $insertQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
				}
			}
		} else {
			echo '{ "errorMsg":"That payment amount would overpay the current share price." }';
		}
	} else {
		echo '{ "errorMsg":"The amount must be a number and the date a date." }';
	}
} else {
	header('Location: ../index.php');
}
?>
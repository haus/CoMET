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
 * This page handles adding and updating payments.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');
	require_once('../includes/functions.php');

	// Sanitize the data.
	if (isset($_POST['date'])) $date = escapeData($DBS['comet'], $_POST['date']);
	if (isset($_POST['memo'])) $memo = escapeData($DBS['comet'], $_POST['memo']);
	if (isset($_POST['ref'])) $reference = escapeData($DBS['comet'], $_POST['ref']);
	if (isset($_POST['amount'])) $amount = escapeData($DBS['comet'], $_POST['amount']);

	// Validate the data a bit.
	if (isset($_POST['value']) && isset($_POST['id'])) {
		$paymentID = (int) $_POST['id'];
		// Get information about the payment to be edited
		$paymentQ = sprintf("SELECT memo FROM payments WHERE paymentID=%u", $paymentID);
		$paymentR = mysqli_query($DBS['comet'], $paymentQ);
		
		if (!$paymentR) printf('Query: %s, Error: %s', $paymentQ, mysqli_error($DBS['comet']));
		
		list($oldValue) = mysqli_fetch_row($paymentR);
		
		// Check the level of the current user. If the user wrote the note or is of level 4 or greater, edit the note.
		if ($oldValue != $_POST['value']) {
			$updateQ = sprintf("UPDATE payments SET memo = '%s', userID=%u WHERE paymentID = %u",
				escapeData($DBS['comet'], $_POST['value']), $_SESSION['userID'], $paymentID);
			$updateR = mysqli_query($DBS['comet'], $updateQ);
			
			if (!$updateR) printf('Query: %s, Error: %s', $updateQ, mysqli_error($DBS['comet']));
			else {
				echo $_POST['value'];
			}
		} else {
			echo $oldValue;
		}
	} elseif (isset($_POST['removeID']) && is_numeric($_POST['removeID'])) {
		$paymentQ = sprintf("DELETE FROM payments WHERE paymentID=%u LIMIT 1",
			escapeData($DBS['comet'], $_POST['removeID'])
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
	// Non empty, numeric amount, non empty actual date.
	} elseif (!empty($date) && !empty($amount) && is_numeric($amount) && checkdate(substr($date, 0, 2), substr($date, 3, 2), substr($date, 6, 4))) { 
		$year = substr($date, 6, 4);
		$month = substr($date, 0, 2);
		$day = substr($date, 3, 2);
		$date = "$year-$month-$day";
		
		$checkQ = "SELECT SUM(p.amount), MAX(date), d.nextPayment, d.joined, d.sharePrice, d.paymentPlan, pp.frequency, pp.amount 
			FROM payments AS p 
				RIGHT JOIN details AS d ON (d.cardNo = p.cardNo) 
				INNER JOIN paymentPlans AS pp ON (d.paymentPlan = pp.planID)
			WHERE d.cardNo={$_SESSION['cardNo']}
			GROUP BY d.cardNo";
		$checkR = mysqli_query($DBS['comet'], $checkQ);
		
		list($total, $last, $next, $trash, $sPrice, $pPlan, $pFreq, $pAmount) = mysqli_fetch_row($checkR);
		$total = (is_null($total) ? 0 : $total);
		$sPrice = (is_null($sPrice) ? $_SESSION['sharePrice'] : $sPrice);
		
		if (($sPrice - $total) >= $amount) { // The payment won't overpay the share. Good to go.
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
					$newMemType = 1;
				} else { // If not fully paid, subscriber
					$newMemType = 2;
				}
					
				$selectQ = "SELECT personNum, memType, staff
					FROM owners
					WHERE cardNo = {$_SESSION['cardNo']}";
				$selectR = mysqli_query($DBS['comet'], $selectQ);

				while (list($personNum, $memType, $staff) = mysqli_fetch_row($selectR)) {
					if ($memType == 1 || $memType == 2) {
						if ($staff == 0 || $staff == 2 || $staff == 3 || $staff == 6) {
							$newDisc = 2;
						} elseif ($staff == 1 || $staff == 4 || $staff == 5) {
							$newDisc = 15;
						} else {
							$newDisc = 0;
						}
					}
					if ($memType != 6 && $memType != 7) {
						if (!updateOwner($_SESSION['cardNo'], $personNum, NULL, NULL, $newDisc, $newMemType, NULL, NULL, NULL, $_SESSION['userID']))
								echo '{ "errorMsg": "failure on ' . $personNum . ' inserted" }';
					}
				}
				
				// Then update the details.
				// Payment Plans Update
				$planQ = sprintf("SELECT planID FROM paymentPlans WHERE amount=%s LIMIT 1",$amount);
				$planR = mysqli_query($DBS['comet'], $planQ);
				
				if (mysqli_num_rows($planR) > 0)
					list($plan) = mysqli_fetch_row($planR);
				else
					$plan = $_SESSION['defaultPlan'];
				
				// Next Payment Logic
				if ($amount >= 30) {
					$period = 12;
				} elseif ($amount >= 15) {
					$period = 6;
				} elseif ($amount >= 5) {
					$period = 1;
				} else {
					$period = 0;
				}
				
				// If paid up fully, next due = null
				if (($total + $amount) == $sPrice) { // If fully paid, shareholder.
					$nextDue = 'NULL';

				// Okay this part requires PHP 5.3 or greater. Date Time work.
				// If after next due or null, next due = payment date + (12/period)
				} elseif (is_null($next) || (strtotime($date) > strtotime($next))) {
					$nextDue = date_create($date);
					$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
					$nextDue = date_format($nextDue, 'Y-m-d');

				// If before or on next due, next due = next due + (12/period)	
				} elseif (strtotime($date) <= strtotime($next)) {
					$nextDue = date_create($next);
					$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
					$nextDue = date_format($nextDue, 'Y-m-d');
				}
				
				if (updateDetails($_SESSION['cardNo'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, $nextDue, $plan, NULL, NULL, $_SESSION['userID']))
					echo '{ "success": "success!" }';
				else
					printf('{ "errorMsg":"MySQL Error: %s"}', mysqli_error($DBS['comet']));;
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
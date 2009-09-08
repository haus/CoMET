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
	} elseif (!empty($date) && !empty($amount) && is_numeric($amount) && checkdate(substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4))) { // Non empty, numeric amount, non empty actual date.
		$checkQ = "SELECT SUM(p.amount), MAX(date), d.nextPayment, d.joined, d.sharePrice, d.paymentPlan, pp.frequency, pp.amount 
			FROM payments AS p 
				RIGHT JOIN details AS d ON (d.cardNo = p.cardNo) 
				INNER JOIN paymentPlans AS pp ON (d.paymentPlan = pp.planID)
			WHERE d.cardNo={$_SESSION['cardNo']}";
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
				echo '{ "success": "success!" }';
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
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

	// Get the payments plans and populate a drop-down menu.

	$planQ = "SELECT * FROM paymentPlans ORDER BY planID ASC";
	$planR = mysqli_query($DBS['comet'], $planQ);
	if (!$planR) printf('Query: %s, Error: %s', $planQ, mysqli_error($DBS['comet']));

	while (list($planID, $freq, $amount) = mysqli_fetch_row($planR)) {
		$plan[$planID] = sprintf('%s',
			($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
		);
	}

	if (isset($_POST['id']) && $_POST['id'] == 'editPrice') {
		$sharePriceQ = "SELECT sharePrice FROM details WHERE cardNo={$_SESSION['cardNo']}";
		$sharePriceR = mysqli_query($DBS['comet'], $sharePriceQ);
		list($sharePrice) = mysqli_fetch_row($sharePriceR);
	
		if (is_numeric($_POST['value']) && $_POST['value'] != $sharePrice) {
			$newPrice = (double)$_POST['value'];
			$updateQ = "UPDATE raw_details SET endDate=curdate() WHERE cardNo={$_SESSION['cardNo']} AND endDate IS NULL";
			$updateR = mysqli_query($DBS['comet'], $updateQ);
	
			if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
				$insertQ = "INSERT INTO raw_details (
					SELECT cardNo, address, phone, city, state, zip, email, nextPayment, paymentPlan, joined, $newPrice, curdate(), 
						NULL, {$_SESSION['userID']}, NULL 
						FROM raw_details 
						WHERE cardNo={$_SESSION['cardNo']} 
							AND DATE(endDate) = curdate() 
							AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo={$_SESSION['cardNo']}) 
						GROUP BY cardNo HAVING MAX(endDate))";
				$insertR = mysqli_query($DBS['comet'], $insertQ);
				if ($insertR)
					echo number_format($newPrice, 2);
				else
					echo number_format($sharePrice, 2);
			}
		} else {
			echo number_format($sharePrice, 2);
		}
	} elseif (isset($_POST['id']) && $_POST['id'] == 'editPlan') {
		$planQ = "SELECT paymentPlan FROM details WHERE cardNo={$_SESSION['cardNo']}";
		$planR = mysqli_query($DBS['comet'], $planQ);
		if (mysqli_num_rows($planR) == 1) { // If the record exists
			list($curPlan) = mysqli_fetch_row($planR);
	
			if (is_numeric($_POST['value']) && $_POST['value'] != $curPlan) {
				$newPlan = (int)$_POST['value'];
				$updateQ = "UPDATE raw_details SET endDate=curdate() WHERE cardNo={$_SESSION['cardNo']} AND endDate IS NULL";
				$updateR = mysqli_query($DBS['comet'], $updateQ);

				if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
					$insertQ = "INSERT INTO raw_details (
						SELECT cardNo, address, phone, city, state, zip, email, nextPayment, $newPlan, joined, sharePrice, curdate(), 
							NULL, {$_SESSION['userID']}, NULL 
							FROM raw_details 
							WHERE cardNo={$_SESSION['cardNo']} 
								AND DATE(endDate) = curdate() 
								AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo={$_SESSION['cardNo']}) 
							GROUP BY cardNo HAVING MAX(endDate))";
					$insertR = mysqli_query($DBS['comet'], $insertQ);
					if ($insertR)
						echo $plan[$newPlan];
					else
						echo $plan[$curPlan];
				}
			} else {
				echo $plan[$curPlan];
			}
		} else {
			echo $plan[$_SESSION['defaultPlan']];
		}
	} elseif (isset($_POST['id']) && $_POST['id'] == 'editJoined') {
		$dateQ = "SELECT joined FROM details WHERE cardNo={$_SESSION['cardNo']}";
		$dateR = mysqli_query($DBS['comet'], $dateQ);
		
		list($oldDate) = mysqli_fetch_row($dateR);
		
		$newYear = (int) substr($_POST['value'], 6, 4);
		$newMonth = str_pad((int) substr($_POST['value'], 0, 2), 2, 0, STR_PAD_LEFT);
		$newDay = str_pad((int) substr($_POST['value'], 3, 2), 2, 0, STR_PAD_LEFT);
		
		$newDate = (checkdate($newMonth,$newDay,$newYear) ? "$newYear-$newMonth-$newDay" : FALSE);
		
		if ($newDate) {
			$updateQ = "UPDATE raw_details SET endDate=curdate() WHERE cardNo={$_SESSION['cardNo']} AND endDate IS NULL";
			$updateR = mysqli_query($DBS['comet'], $updateQ);

			if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
				$insertQ = "INSERT INTO raw_details (
					SELECT cardNo, address, phone, city, state, zip, email, nextPayment, paymentPlan, '$newDate', sharePrice, curdate(), 
						NULL, {$_SESSION['userID']}, NULL 
						FROM raw_details 
						WHERE cardNo={$_SESSION['cardNo']} 
							AND DATE(endDate) = curdate() 
							AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo={$_SESSION['cardNo']}) 
						GROUP BY cardNo HAVING MAX(endDate))";
				$insertR = mysqli_query($DBS['comet'], $insertQ);
				if ($insertR)
					echo date('m/d/Y', strtotime($newDate));
				else
					echo date('m/d/Y', strtotime($oldDate));
			}
		}
	} elseif (isset($_POST['id']) && $_POST['id'] == 'editNext') {
			$dateQ = "SELECT nextPayment FROM details WHERE cardNo={$_SESSION['cardNo']}";
			$dateR = mysqli_query($DBS['comet'], $dateQ);

			list($oldDate) = mysqli_fetch_row($dateR);

			$newYear = (int) substr($_POST['value'], 6, 4);
			$newMonth = str_pad((int) substr($_POST['value'], 0, 2), 2, 0, STR_PAD_LEFT);
			$newDay = str_pad((int) substr($_POST['value'], 3, 2), 2, 0, STR_PAD_LEFT);

			$newDate = (checkdate($newMonth,$newDay,$newYear) ? "$newYear-$newMonth-$newDay" : FALSE);

			if ($newDate) {
				$updateQ = "UPDATE raw_details SET endDate=curdate() WHERE cardNo={$_SESSION['cardNo']} AND endDate IS NULL";
				$updateR = mysqli_query($DBS['comet'], $updateQ);

				if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
					$insertQ = "INSERT INTO raw_details (
						SELECT cardNo, address, phone, city, state, zip, email, '$newDate', paymentPlan, joined, sharePrice, curdate(), 
							NULL, {$_SESSION['userID']}, NULL 
							FROM raw_details 
							WHERE cardNo={$_SESSION['cardNo']} 
								AND DATE(endDate) = curdate() 
								AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo={$_SESSION['cardNo']}) 
							GROUP BY cardNo HAVING MAX(endDate))";
					$insertR = mysqli_query($DBS['comet'], $insertQ);
					if ($insertR)
						echo date('m/d/Y', strtotime($newDate));
					else
						echo date('m/d/Y', strtotime($oldDate));
				}
			}
		}

	if (isset($_GET['plans'])) {
		print json_encode($plan);
	}
} else {
	header('Location: ../index.php');
}
?>
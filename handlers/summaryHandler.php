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
 * This page handles changes to the details of a record from the summary div in the main tab.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');
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
	
		if (is_numeric($_POST['value']) && $_POST['value'] != $sharePrice && $_POST['value'] >= 0) {
			$newPrice = (double)$_POST['value'];
			if (updateDetails($_SESSION['cardNo'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $newPrice, $_SESSION['userID']))
				echo number_format($newPrice, 2);
			else
				echo number_format($sharePrice, 2);
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
				if (updateDetails($_SESSION['cardNo'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $newPlan, NULL, NULL, $_SESSION['userID']))
					echo $plan[$newPlan];
				else
					echo $plan[$curPlan];
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
			if (updateDetails($_SESSION['cardNo'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $newDate, NULL, $_SESSION['userID']))
				echo date('m/d/Y', strtotime($newDate));
			else
				echo date('m/d/Y', strtotime($oldDate));
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
				if (updateDetails($_SESSION['cardNo'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, $newDate, NULL, NULL, NULL, $_SESSION['userID']))
					echo date('m/d/Y', strtotime($newDate));
				else
					echo date('m/d/Y', strtotime($oldDate));
			}
		}

	if (isset($_GET['plans'])) {
		print json_encode($plan);
	}
} else {
	header('Location: ../index.php');
}
?>
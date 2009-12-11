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

require_once('config.php');

// First set the join date based on the first share payment.
$joinDateQ = "SELECT MIN(date), cardNo, amount
	FROM payments
	GROUP BY cardNo 
		HAVING amount > 0
	ORDER BY cardNo";
$joinDateR = mysqli_query($DBS['comet'], $joinDateQ);

if (!$joinDateR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $joinDateQ);
else {
	while (list($date, $cardNo) = mysqli_fetch_row($joinDateR)) {
		// Set join date to first payment date...
		//printf('Join Date: %s, Card No: %s<br />', $date, $cardNo);
		
		$updateQ = sprintf("UPDATE raw_details SET endDate=curdate() WHERE cardNo=%u AND endDate IS NULL", $cardNo);
		$updateR = mysqli_query($DBS['comet'], $updateQ);
		
		if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
			$insertQ = sprintf("INSERT INTO raw_details (
				SELECT cardNo, address, phone, city, state, zip, email, noMail, nextPayment, paymentPlan, '%s', sharePrice, curdate(), 
					NULL, 0, NULL 
					FROM raw_details
					WHERE cardNo=%u 
						AND DATE(endDate) = curdate() 
						AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo=%u) 
					GROUP BY cardNo HAVING MAX(endDate))", 
					$date, $cardNo, $cardNo);
			$insertR = mysqli_query($DBS['comet'], $insertQ);
		
			if (!$insertR)
				echo '{ "errorMsg":"Query: ' . $insertQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		} else {
			echo '{ "errorMsg":"Query: ' . $updateQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		}
	}
}

// Then set nextDue...
$nextDueQ = "SELECT cardNo, amount, date 
	FROM payments
	WHERE amount > 0
	ORDER BY date ASC";
$nextDueR = mysqli_query($DBS['comet'], $nextDueQ);

if (!$nextDueR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $nextDueQ);
else {
	while (list($cardNo, $amount, $date) = mysqli_fetch_row($nextDueR)) {
		// Get current next due date...
		$currQ = "SELECT nextPayment FROM details WHERE cardNo = $cardNo";
		$currR = mysqli_query($DBS['comet'], $currQ);
		
		if (!$currR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $currQ);
		else {
			list($next) = mysqli_fetch_row($currR);
			
			// How far ahead to add the date...
			if ($amount >= 30) {
				$period = 12;
			} elseif ($amount >= 15) {
				$period = 6;
			} elseif ($amount >= 5) {
				$period = 1;
			} else {
				$period = 0;
			}
			
			// Which date to use...
			if (is_null($next) || (strtotime($next) < strtotime($date))) {
				// Move forward from payment date...
				$nextDue = date_create($date);
				$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
				$nextDue = date_format($nextDue, 'Y-m-d');
			} else {
				// Move forward from next due date...
				$nextDue = date_create($next);
				$nextDue = date_add($nextDue, new DateInterval("P" . $period . "M"));
				$nextDue = date_format($nextDue, 'Y-m-d');
			}
			
			$updateQ = sprintf("UPDATE raw_details SET endDate=curdate() WHERE cardNo=%u AND endDate IS NULL", $cardNo);
			$updateR = mysqli_query($DBS['comet'], $updateQ);

			if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
				$insertQ = sprintf("INSERT INTO raw_details (
					SELECT cardNo, address, phone, city, state, zip, email, noMail, '%s', paymentPlan, joined, sharePrice, curdate(), 
						NULL, 0, NULL 
						FROM raw_details
						WHERE cardNo=%u 
							AND DATE(endDate) = curdate() 
							AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo=%u) 
						GROUP BY cardNo HAVING MAX(endDate))", 
						$nextDue, $cardNo, $cardNo);
				$insertR = mysqli_query($DBS['comet'], $insertQ);

				if (!$insertR)
					echo '{ "errorMsg":"Query: ' . $insertQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
			} else {
				echo '{ "errorMsg":"Query: ' . $updateQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
			}
		}
		
		//printf('Original Due Date: %s, Next Due Date: %s, Payment Date: %s, Amount: $%s, Card No: %s<br />', $next, $nextDue, $date, $amount, $cardNo);
	}
}

// Set payment plans...
$planArrayQ = "SELECT planID, amount FROM paymentPlans ORDER BY planID ASC";
$planArrayR = mysqli_query($DBS['comet'], $planArrayQ);

if (!$planArrayR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $planArrayQ);
else {
	$planArray = array();
	while (list($planID, $amount) = mysqli_fetch_row($planArrayR)) {
		$planArray[$planID] = $amount;
	}
}

// Set payment plan...
$planQ = "SELECT date, cardNo, amount
	FROM payments
	GROUP BY cardNo 
		HAVING MAX(date) AND amount > 0
	ORDER BY cardNo";
$planR = mysqli_query($DBS['comet'], $planQ);

if (!$planR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $planQ);

while (list($date, $cardNo, $amount) = mysqli_fetch_row($planR)) {
	$key = array_search($amount, $planArray);
	if ($key) {
		$updateQ = sprintf("UPDATE raw_details SET endDate=curdate() WHERE cardNo=%u AND endDate IS NULL", $cardNo);
		$updateR = mysqli_query($DBS['comet'], $updateQ);
		
		if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
			$insertQ = sprintf("INSERT INTO raw_details (
				SELECT cardNo, address, phone, city, state, zip, email, noMail, nextPayment, %u, joined, sharePrice, curdate(), 
					NULL, 0, NULL 
					FROM raw_details
					WHERE cardNo=%u 
						AND DATE(endDate) = curdate() 
						AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo=%u) 
					GROUP BY cardNo HAVING MAX(endDate))", 
					$key, $cardNo, $cardNo);
			$insertR = mysqli_query($DBS['comet'], $insertQ);
		
			if (!$insertR)
				echo '{ "errorMsg":"Query: ' . $insertQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		} else {
			echo '{ "errorMsg":"Query: ' . $updateQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		}
		
		// printf('%s <br />', $updateQ);
	} else {
		printf('Card No: %s, No Plan Match <br />', $cardNo);
	}
}

// Set fully paid members to no next due date...
$fullQ = "SELECT d.cardNo, SUM(p.amount) AS paid, d.shareprice AS price 
	FROM details AS d 
	INNER JOIN payments AS p 
		ON d.cardNo = p.cardNo 
	GROUP BY cardno 
		HAVING paid = price 
	ORDER BY cardno ASC";
$fullR = mysqli_query($DBS['comet'], $fullQ);

if (!$fullR) printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $fullQ);
else {
	while (list($cardNo, $amount, $price) = mysqli_fetch_row($fullR)) {
		$updateQ = sprintf("UPDATE raw_details SET endDate=curdate() WHERE cardNo=%u AND endDate IS NULL", $cardNo);
		$updateR = mysqli_query($DBS['comet'], $updateQ);
		
		if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
			$insertQ = sprintf("INSERT INTO raw_details (
				SELECT cardNo, address, phone, city, state, zip, email, noMail, NULL, paymentPlan, joined, sharePrice, curdate(), 
					NULL, 0, NULL 
					FROM raw_details
					WHERE cardNo=%u 
						AND DATE(endDate) = curdate() 
						AND id=(SELECT MAX(id) FROM raw_details WHERE cardNo=%u) 
					GROUP BY cardNo HAVING MAX(endDate))", 
					$cardNo, $cardNo);
			$insertR = mysqli_query($DBS['comet'], $insertQ);
		
			if (!$insertR)
				echo '{ "errorMsg":"Query: ' . $insertQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		} else {
			echo '{ "errorMsg":"Query: ' . $updateQ . ', MySQL Error: ' . mysqli_error($DBS['comet']) . '" }';
		}
		
		//printf('%s <br />', $updateQ);
	}
}
?>
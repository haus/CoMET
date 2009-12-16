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

$from = sprintf('%s <%s>', $_SESSION['reminderFrom'], $_SESSION['reminderEmail']);
$from = 'Matthaus Litteken <matthaus@albertagrocery.coop>';
$host = $_SESSION['smtpHost'];
$user = $_SESSION['smtpUser'];
$pass = $_SESSION['smtpPass'];

$to = ' <mlitteken@gmail.com>';

$search = array('[firstName]', '[lastName]', '[dueDate]', '[balance]', '[paymentPlan]');
$count = 0;

$reminders = array();

// First inactives...owners who will be made inactive...
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
	$newTo = $first . ' ' . $last . $to;
	$body = str_replace($search, $replace, $inactiveMsg);
	
	$reminders[] = array(
		'to' => $newTo, 
		'from' => $from, 
		'subject' => $inactiveSubject, 
		'body' => $body
		);
		
	$count++;
}
		
// Then coming due...reminder that they should make a payment...
$comingDueQ = sprintf(
	'SELECT d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, \'%%M %%e, %%Y\'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType = 2
		AND o.personNum = 1
		AND d.email IS NOT NULL AND d.email <> \'\'
	GROUP BY cardNo
		HAVING diff = -%u', $_SESSION['comingDueDays']);
$comingDueR = mysqli_query($DBS['comet'], $comingDueQ);

$comingDueMsg = $_SESSION['comingDueMsg'];
$comingDueSubject = $_SESSION['comingDueSubject'];

if (!$comingDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $comingDueQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($comingDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$newTo = $first . ' ' . $last . $to;
	$body = str_replace($search, $replace, $comingDueMsg);
	
	$reminders[] = array(
		'to' => $newTo, 
		'from' => $from, 
		'subject' => $comingDueSubject, 
		'body' => $body
		);
		
	$count++;
}

// Then past due...reminder that they will be put on hold...
$pastDueQ = sprintf(
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
		HAVING diff = %u', $_SESSION['pastDueDays']);
$pastDueR = mysqli_query($DBS['comet'], $pastDueQ);

$pastDueMsg = $_SESSION['pastDueMsg'];
$pastDueSubject = $_SESSION['pastDueSubject'];

if (!$pastDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $pastDueQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($pastDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$newTo = $first . ' ' . $last . $to;
	$body = str_replace($search, $replace, $pastDueMsg);
	
	$reminders[] = array(
		'to' => $newTo, 
		'from' => $from, 
		'subject' => $pastDueSubject, 
		'body' => $body
		);
		
	$count++;
}

$mailed = cometMail($reminders, 'reminder');

if ($mailed == $count)
	echo "Success, $count reminder mails sent.";
else
	echo "Failure, only $mailed send of $count attempts.";
	
?>
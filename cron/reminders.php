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
 * This cron script generates reminder emails and adds them to the mail queue.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

$baseDir = (substr(__DIR__, -1) == '/' ? substr(__DIR__, 0, -1) : __DIR__);
$baseDir = substr(__DIR__, 0, strrpos($baseDir, '/'));

require_once($baseDir . '/includes/config.php');
require_once($baseDir . '/includes/functions.php');

$from = sprintf('"%s" <%s>', $_SESSION['reminderFrom'], $_SESSION['reminderEmail']);
$host = $_SESSION['smtpHost'];
$user = $_SESSION['smtpUser'];
$pass = $_SESSION['smtpPass'];

$search = array('[firstName]', '[lastName]', '[dueDate]', '[balance]', '[paymentPlan]');
$count = 0;

$reminders = array();

$badEmailList = NULL;

// First inactives...owners who will be made inactive...
$inactiveQ = sprintf(
	"SELECT d.cardNo, d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, '%%M %%e, %%Y'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2,5)
		AND o.personNum = 1
	GROUP BY d.cardNo 
		HAVING diff >= %u", $_SESSION['inactiveDays']);
$inactiveR = mysqli_query($DBS['comet'], $inactiveQ);

$inactiveMsg = $_SESSION['inactiveMsg'];
$inactiveSubject = $_SESSION['inactiveSubject'];

if (!$inactiveR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $inactiveQ);

while (list($cardNo, $email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($inactiveR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	
	$to = sprintf('"%s %s" <%s>', $first, $last, $email);
	$body = str_replace($search, $replace, $inactiveMsg);
	
	if (is_null($email) || empty($email) || strpos($email, '@') === false) {
		$badEmailList .= sprintf("Card No: %u, Email Address: %s, Name: %s\n", 
			$cardNo, (is_null($email) || empty($email) ? 'No email' : $email), $first . " " . $last);
	} else {
		$reminders[] = array(
			'to' => $to, 
			'from' => $from, 
			'subject' => $inactiveSubject, 
			'body' => $body
			);
		
		$count++;
	}
}
		
// Then coming due...reminder that they should make a payment...
$comingDueQ = sprintf(
	"SELECT d.cardNo, d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, '%%M %%e, %%Y'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2)
		AND o.personNum = 1
	GROUP BY d.cardNo
		HAVING diff = -%u", $_SESSION['comingDueDays']);
$comingDueR = mysqli_query($DBS['comet'], $comingDueQ);

$comingDueMsg = $_SESSION['comingDueMsg'];
$comingDueSubject = $_SESSION['comingDueSubject'];

if (!$comingDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $comingDueQ);

while (list($cardNo, $email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($comingDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$to = sprintf('"%s %s" <%s>', $first, $last, $email);
	$body = str_replace($search, $replace, $comingDueMsg);
	
	if (is_null($email) || empty($email) || strpos($email, '@') === false) {
		$badEmailList .= sprintf("Card No: %u, Email Address: %s, Name: %s\n", 
			$cardNo, (is_null($email) || empty($email) ? 'No email' : $email), $first . " " . $last);
	} else {
		$reminders[] = array(
			'to' => $to, 
			'from' => $from, 
			'subject' => $comingDueSubject, 
			'body' => $body
			);
		
		$count++;
	}
}

// Then past due...reminder that they will be put on hold...
$pastDueQ = sprintf(
	"SELECT d.cardNo, d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, '%%M %%e, %%Y'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2,5)
		AND o.personNum = 1
	GROUP BY d.cardNo
		HAVING diff = %u", $_SESSION['pastDueDays']);
$pastDueR = mysqli_query($DBS['comet'], $pastDueQ);

$pastDueMsg = $_SESSION['pastDueMsg'];
$pastDueSubject = $_SESSION['pastDueSubject'];

if (!$pastDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $pastDueQ);

while (list($cardNo, $email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($pastDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$to = sprintf('"%s %s" <%s>', $first, $last, $email);
	$body = str_replace($search, $replace, $pastDueMsg);
	
	if (is_null($email) || empty($email) || strpos($email, '@') === false) {
		$badEmailList .= sprintf("Card No: %u, Email Address: %s, Name: %s\n", 
			$cardNo, (is_null($email) || empty($email) ? 'No email' : $email), $first . " " . $last);
	} else {
		$reminders[] = array(
			'to' => $to, 
			'from' => $from, 
			'subject' => $pastDueSubject, 
			'body' => $body
			);
		
		$count++;
	}
}

if (!empty($badEmailList) || !is_null($badEmailList)) {
	$body = "Here is a list of the invalid or empty emails from the reminders on " . date('m/d/Y') . "\n\n". $badEmailList;
	$badMail[] = array(
		'to' => $from,
		'from' => sprintf('CoMET <%s>', $_SESSION['systemUser']),
		'subject' => 'List of invalid emails from nightly reminders',
		'body' => $body);
		
	cometMail($badMail, 'system');
}

$mailed = cometMail($reminders, 'reminder');

if ($mailed == $count)
	echo "Success, $count reminder mails sent.";
else
	echo "Failure, only $mailed send of $count attempts.";
	
?>
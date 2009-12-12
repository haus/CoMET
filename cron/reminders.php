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
require_once('../includes/config.php');
require_once('../includes/functions.php');
require_once('Mail.php');

$from = sprintf('%s <%s>', $_SESSION['reminderFrom'], $_SESSION['reminderEmail']);
$from = 'Matthaus Litteken <matthaus@albertagrocery.coop>';
$host = $_SESSION['smtpHost'];
$user = $_SESSION['smtpUser'];
$pass = $_SESSION['smtpPass'];

$to = ' <mlitteken@gmail.com>';
$subject = 'reminder message';

$search = array('[firstName]', '[lastName]', '[dueDate]', '[balance]', '[paymentPlan]');


// First inactives...owners who will be made inactive...
$inactiveQ = sprintf(
	'SELECT d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, \'%%M %%e, %%Y\'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2,3)
		AND o.personNum = 1
	GROUP BY cardNo 
		HAVING diff >= %u', $_SESSION['inactiveDays']);
$inactiveR = mysqli_query($DBS['comet'], $inactiveQ);

$inactiveMsg = $_SESSION['inactiveMsg'];

if (!$inactiveR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $inactiveQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($inactiveR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$body = str_replace($search, $replace, $inactiveMsg);
	echo $body . "\n";
}
		
// Then coming due...reminder that they should make a payment...
$comingDueQ = sprintf(
	'SELECT d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, \'%%M %%e, %%Y\'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2,3)
		AND o.personNum = 1
	GROUP BY cardNo
		HAVING diff BETWEEN -20 AND -%u', $_SESSION['comingDueDays']);
$comingDueR = mysqli_query($DBS['comet'], $comingDueQ);

$comingDueMsg = $_SESSION['comingDueMsg'];

if (!$comingDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $comingDueQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($comingDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$body = str_replace($search, $replace, $comingDueMsg);
	echo $body . "\n";
}

// Then past due...reminder that they will be put on hold...
$pastDueQ = sprintf(
	'SELECT d.email, o.firstName, o.lastName, d.sharePrice, pp.amount, SUM(p.amount), 
		DATE_FORMAT(nextPayment, \'%%M %%e, %%Y\'), TIMESTAMPDIFF(DAY, nextPayment, curdate()) AS diff, o.cardNo 
	FROM details AS d 
		INNER JOIN owners AS o ON d.cardNo = o.cardNo 
		INNER JOIN payments AS p ON p.cardNo = d.cardNo
		INNER JOIN paymentPlans AS pp ON pp.planID = d.paymentPlan
	WHERE o.memType IN (1,2,3)
		AND o.personNum = 1
	GROUP BY cardNo
		HAVING diff BETWEEN %u AND 20', $_SESSION['pastDueDays']);
$pastDueR = mysqli_query($DBS['comet'], $pastDueQ);

$pastDueMsg = $_SESSION['pastDueMsg'];

if (!$pastDueR)
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $pastDueQ);

while (list($email, $first, $last, $sPrice, $planAmount, $paid, $nextDue, $daysLate) = mysqli_fetch_row($pastDueR)) {
	$replace = array($first, $last, $nextDue, '$' . number_format($sPrice-$paid, 2), '$' . $planAmount);
	$body = str_replace($search, $replace, $pastDueMsg);
	echo $body . "\n";
}

exit();

$headers = array ('From' => $from,
  'To' => $to,
  'Subject' => $subject);

$smtp = Mail::factory('smtp',
  array ('host' => $host,
    'auth' => true,
    'username' => $user,
    'password' => $pass));

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {
  echo("<p>" . $mail->getMessage() . "</p>");
 } else {
  echo("<p>Message successfully sent!</p>");
 }
?>
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

if (isset($_POST['submitted'])) {
	switch($_POST['testType']) {
		case 'smtpTest':
			require_once('Mail.php');
			cometMail();
			$from = "Matthaus <matthaus@albertagrocery.coop>";
			$to = "Matthaus <mlitteken@gmail.com>";
			$subject = "Testing...";
			$body = "Testing...";

			$host = "smtp.albertagrocery.coop";
			$user = "matthaus@albertagrocery.coop";
			$pass = "lung*vIa";

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
			
			break;
		case 'opTest':
			$opQ = "SELECT name, value FROM options WHERE name IN ('opHost', 'opUser', 'opPass', 'opDB')";
			$opR = mysqli_query($DBS['comet'], $opQ);
	
			if (!$opR) {
				printf('{"errorMsg": "MySQL Error: %s, Query: %s"}', mysqli_error($DBS['comet']), $opQ);
				exit();
			} else {
				while (list($name, $value) = mysqli_fetch_row($opR)) {
					$db[$name] = $value;
				}
		
				printf('{"opResult": "%s"}', 
					(mysqli_connect($db['opHost'], $db['opUser'], $db['opPass'], $db['opDB']) 
						? 'Successfully connected.' : 'Connection failure: please check connection details.'));
				exit();
			}
			
			break;
			
		case 'logTest':
			$logQ = "SELECT name, value FROM options WHERE name IN ('logHost', 'logUser', 'logPass', 'logDB')";
			$logR = mysqli_query($DBS['comet'], $logQ);
	
			if (!$logR) {
				printf('{"errorMsg": "MySQL Error: %s, Query: %s"}', mysqli_error($DBS['comet']), $logQ);
				exit();
			} else {
				while (list($name, $value) = mysqli_fetch_row($logR)) {
					$db[$name] = $value;
				}
		
				printf('{"logResult": "%s"}', 
					(mysqli_connect($db['logHost'], $db['logUser'], $db['logPass'], $db['logDB']) 
						? 'Successfully connected.' : 'Connection failure: please check connection details.'));
				exit();
			}
			
			break;

		default:
			print_r($_POST);
			break;
	}
} else {
	$allowed = array('smtpUser', 'smtpPass', 'smtpHost', 
		'opHost', 'opUser', 'opPass', 'opDB', 
		'logHost', 'logUser', 'logPass', 'logDB',
		'houseHoldSize', 'discounts', 'sharePrice', 'defaultPayment', 'defaultPlan');
	
	$passArray = array('smtpPass', 'opPass', 'logPass');
	$numericArray = array('houseHoldSize', 'defaultPlan', 'sharePrice', 'defaultPayment');

	if (isset($_POST['id']) && isset($_POST['value']) && in_array($_POST['id'], $allowed)) {
		$id = escape_data($DBS['comet'], $_POST['id']);
		$value = escape_data($DBS['comet'], $_POST['value']);
	} else {
		$id = NULL;
		$value = NULL;
	}

	if (!empty($id) && $value) {
		$valueQ = "SELECT value FROM options WHERE name='$id'";
		$valueR = mysqli_query($DBS['comet'], $valueQ);
		list($oldValue) = mysqli_fetch_row($valueR);
	
		if (empty($value)) {
			// If empty or non-numeric when supposed to be then load and display the initial value...
			echo (in_array($id, $passArray) ? '(hidden)' : $oldValue);
			exit();
		} else {
			$updateQ = sprintf("UPDATE options SET value='%s' WHERE name='%s'", $value, $id);
			$updateR = mysqli_query($DBS['comet'], $updateQ);
			if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
				echo (in_array($id, $passArray) ? '(hidden)' : $value);
			} else
				echo (in_array($id, $passArray) ? '(hidden)' : $oldValue);
		}
	}

	if (empty($value) && in_array($id, $passArray)) {
		echo '(hidden)';
	}
}
?>
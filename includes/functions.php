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

/**
 * Functions repository for CoMET. Holds important and generalized functions.
 * Abstracts owner and details updates and insertion.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

/**
 * addOwner function: Inserts a new owner into the database. Returns true on success, false on failure.
 * @param integer $cardNo cardNo of the record to be inserted
 * @param integer $personNum personNum of the record to be inserted
 * @param string $firstName First name of the record to be inserted, is sanitized with escape_data
 * @param string $lastName Last name of the record to be inserted, is sanitized with escape_data
 * @param integer $discount Discount of the record to be inserted, is cast to an integer
 * @param integer $memType Member type of the record to be inserted, is cast to an integer
 * @param integer $staff Staff level of the record to be inserted, is cast to an integer
 * @param boolean $chargeOk House charge boolean of the record to be inserted, is cast to an integer
 * @param boolean $writeChecks Write check boolean of the record to be inserted, is cast to an integer
 * @param integer $userID User ID of the user who added the record
 * @return boolean true on success, false on failure
 */
function addOwner($cardNo, $personNum, $firstName, $lastName, $discount, $memType, $staff, $chargeOk, $writeChecks, $userID) {
	global $DBS;
	
	// Insert the new record...
	$insertQ = sprintf(
		"INSERT INTO raw_owners VALUES (%u, %u, '%s', '%s', %u, %u, %u, %u, %u, curdate(), NULL, %u, NULL)",
			$cardNo,
			$personNum,
			escape_data($firstName),
			escape_data($lastName),
			(int) $discount,
			(int) $memType,
			(int) $staff,
			(int) $chargeOk,
			(int) $writeChecks,
			$userID
	);
	$insertR = mysqli_query($DBS['comet'], $insertQ);
	
	if ($insertR)
		return true;
	else
		return false;
}

/**
 * updateOwner function: Updates an owner in the database. First updates the old record, then inserts the updated record. 
 * Returns true on success, false on failure.
 * @param integer $cardNo cardNo of the record to be inserted
 * @param integer $personNum personNum of the record to be inserted
 * @param string $firstName First name of the record to be inserted, is sanitized with escape_data. If null, old value is used.
 * @param string $lastName Last name of the record to be inserted, is sanitized with escape_data. If null, old value is used.
 * @param integer $discount Discount of the record to be inserted, is cast to an integer. If null, old value is used.
 * @param integer $memType Member type of the record to be inserted, is cast to an integer. If null, old value is used.
 * @param integer $staff Staff level of the record to be inserted, is cast to an integer. If null, old value is used.
 * @param boolean $chargeOk House charge boolean of the record to be inserted, is cast to an integer. If null, old value is used.
 * @param boolean $writeChecks Write check boolean of the record to be inserted, is cast to an integer. If null, old value is used.
 * @param integer $userID User ID of the user who added the record
 * @return boolean true on success, false on failure
 */
function updateOwner($cardNo, $personNum, $firstName, $lastName, $discount, $memType, $staff, $chargeOk, $writeChecks, $userID) {
	global $DBS;
	$updateQ = sprintf(
		"UPDATE raw_owners SET endDate=curdate() WHERE cardNo=%u AND personNum=%u AND endDate IS NULL", 
		$cardNo, $personNum
		);
	$updateR = mysqli_query($DBS['comet'], $updateQ);
	
	if ($updateR) {
		// Then insert the new entries...
		$insertQ = sprintf(
			"INSERT INTO raw_owners (
			SELECT cardNo, personNum, %s, %s, %u, %u, %u, %u, %u, curdate(), NULL, %u, NULL
				FROM raw_owners
				WHERE cardNo=%u AND DATE(endDate) = curdate() AND personNum = %u GROUP BY cardNo, personNum HAVING MAX(endDate))",
				(is_null($firstName) ? 'firstName' : escape_data($firstName)),
				(is_null($lastName) ? 'lastName' : escape_data($lastName)),
				(is_null($discount) ? 'discount' : (int) $discount),
				(is_null($memType) ? 'memType' : (int) $memType),
				(is_null($staff) ? 'staff' : (int) $staff),
				(is_null($chargeOk) ? 'chargeOk' : (int) $chargeOk),
				(is_null($writeChecks) ? 'writeChecks' : (int) $writeChecks),
				$userID,
				$cardNo,
				$personNum
		);
		$insertR = mysqli_query($DBS['comet'], $insertQ);
		
		if ($insertR)
			return true;
		else
			return false;
	} else {
		return false;
	}	
}

function addDetails() {
	
}

function updateDetails() {
	
}

/**
 * escape_data function: sanitizes trimmed input using mysqli_real_escape_string, if it exists.
 * @param object &$connection reference to the mysqli connection to use in the escaping
 * @param string $data string/data to be escaped for safe insertion into MySQL DB.
 * @return string sanitized data ready for insertion into MySQL DB.
 */
if (!function_exists('escape_data')) {	
	function escape_data(&$connection, $data) {
		if (function_exists('mysqli_real_escape_string'))
			return mysqli_real_escape_string($connection, trim($data));
		else
			return trim($data);
	}
}


function cometMail($mail, $type) {
	require_once(__DIR__ . '/config.php');
	require_once('Mail.php');
	require_once('Mail/Queue.php');

	$host = $_SESSION['smtpHost'];
	
	if ($type == 'system') {
		$user = $_SESSION['systemUser'];
		$pass = $_SESSION['systemPass'];
		
		$smtp = Mail::factory(
			'smtp',
			array (
				'host' => $host,
		    	'auth' => true,
			    'username' => $user,
			    'password' => $pass
			)
		);

		$count = 0;

		foreach ($mail AS $eMail) {
			$headers = array(
				'From' => $eMail['from'],
			  	'To' => $eMail['to'],
			  	'Subject' => $eMail['subject']
			);

			$mail = $smtp->send($eMail['to'], $headers, $eMail['body']);

			if (PEAR::isError($mail) && $type == 'system') {
				echo '<blink>' . $mail->getMessage() . '</blink>';
			} elseif (PEAR::isError($mail) && $type == 'reminder') {
				echo $mail->getMessage() . "\n";
				$count = $count;
			} else {
				$count++;
			}
		}

		return $count;
		
	} elseif ($type == 'reminder') {
		
		/* we use the db_options and mail_options here */
		$mail_queue =& new Mail_Queue($_SESSION["queue_db"], $_SESSION["queue_options"]);

		$count = 0;

		foreach ($mail AS $eMail) {			
			$headers = array(
				'From' => $eMail['from'],
			  	'To' => $eMail['to'],
			  	'Subject' => $eMail['subject']
			);

			/* we use Mail_mime() to construct a valid mail */
			$mime =& new Mail_mime();
			$mime->setTXTBody($eMail['body']);
			$body = $mime->get();
			$headers = $mime->headers($headers);


			/* Put message to queue */
			$mail_queue->put($eMail['from'], $eMail['to'], $headers, $body);
			$count++;
			/*
			if (PEAR::isError($mail) && $type == 'system') {
				echo '<blink>' . $mail->getMessage() . '</blink>';
			} elseif (PEAR::isError($mail) && $type == 'reminder') {
				echo $mail->getMessage() . "\n";
				$count = $count;
			} else {
				$count++;
			}
			*/
		}

		return $count;
		
	}

}
?>
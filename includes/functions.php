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
 *
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 0.1
 * @package CoMET
 */

/**
 * checkPage function: checks if the current page is the passed string. If not, it redirects
 * to index.php.
 * @param $page a string page name to check the current page against
 * @return none
 */
function checkPage($page) {
	if (substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/')+1, 30) !== $page) {
		header('location:../index.php');
		exit();
	}
}

/**
 * escape_data function: sanitizes trimmed input using mysqli_real_escape_string, if it exists.
 * @param &$connection reference to the mysqli connection to use in the escaping
 * @param $data string/data to be escaped for safe insertion into MySQL DB.
 * @return sanitized data ready for insertion into MySQL DB.
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
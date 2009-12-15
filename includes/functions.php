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

function cometMail($to, $from, $subject, $body, $type = 'system') {
	require_once(__DIR__ . '/config.php');
	require_once('Mail.php');

	$host = $_SESSION['smtpHost'];
	
	if ($type == 'system') {
		$user = $_SESSION['systemUser'];
		$pass = $_SESSION['systemPass'];
	} elseif ($type == 'reminder') {
		$user = $_SESSION['smtpUser'];
		$pass = $_SESSION['smtpPass'];
	}

	$headers = array ('From' => $from,
	  'To' => $to,
	  'Subject' => $subject);

	$smtp = Mail::factory(
		'smtp',
		array (
			'host' => $host,
	    	'auth' => true,
		    'username' => $user,
		    'password' => $pass
		)
	);

	$mail = $smtp->send($to, $headers, $body);

	if (PEAR::isError($mail) && $type == 'system') {
		echo '<blink>' . $mail->getMessage() . '</blink>';
	} elseif (PEAR::isError($mail) && $type == 'reminder') {
		return $mail->getMessage();
	} else {
		return 0;
	}
}
?>
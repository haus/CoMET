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
require_once('../includes/mysqli_connect.php');
require_once('../includes/functions.php');

$allowed = array('smtpUser', 'smtpPass', 'smtpHost', 'opHost', 'opUser', 'opPass', 'opDB', 'logHost', 'logUser', 'logPass', 'logDB');
$passArray = array('smtpPass', 'opPass', 'logPass');

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

?>
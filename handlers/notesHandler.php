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

//print_r($_POST);

if (isset($_POST['newMain']) && $_POST['newMain'] == "true") {
	// Validate the note.
	$mainNote = escape_data($DBS['comet'], $_POST['mainNote']);
	
	if (!empty($mainNote)) {
		$noteQ = sprintf("INSERT INTO notes VALUES ('%s', NULL, NULL, %u, now(), %u)",
			$mainNote, $_SESSION['cardNo'], $_SESSION['userID']
			);
		$noteR = mysqli_query($DBS['comet'], $noteQ);
		
		if (!$noteR) {
			printf('{ "errorMsg":"Query: %s, Error: %s" } ',
				$noteQ, 
				mysqli_error($DBS['comet'])
			);
		} else {
			$tID = mysqli_insert_id($DBS['comet']);
			$noteQ = sprintf("UPDATE notes SET parentID=%u WHERE threadID=%u",
				$tID, $tID
				);
			$noteR = mysqli_query($DBS['comet'], $noteQ);
			if (!$noteR) {
				printf('{ "errorMsg":"Query: %s, Error: %s" } ',
					$noteQ, 
					mysqli_error($DBS['comet'])
				);
			} else {
				echo ' { "success": "Note added." } ';
			}
		}
	} else {
		echo ' { "errorMsg": "Cannot add an empty note." } ';
	}
} elseif (isset($_POST['noteID']) && is_numeric($_POST['noteID'])) {
	$parent = (int) $_POST['noteID'];
	$note = escape_data($DBS['comet'], $_POST['note'][$parent]);
	
	if (!empty($note)) {
		$noteQ = sprintf("INSERT INTO notes VALUES ('%s', NULL, %u, %u, now(), %u)",
			$note, $parent, $_SESSION['cardNo'], $_SESSION['userID']
			);
		$noteR = mysqli_query($DBS['comet'], $noteQ);
		
		if (!$noteR) {
			printf('{ "errorMsg":"Query: %s, Error: %s" } ',
				$noteQ, 
				mysqli_error($DBS['comet'])
			);
		} else {
			echo ' { "success": "Reply added." } ';
		}
	} else {
		echo ' { "errorMsg": "Cannot add an empty note." } ';
	}
	
} elseif (isset($_POST['removeID']) && is_numeric($_POST['removeID'])) {
		$noteQ = sprintf("DELETE FROM notes WHERE threadID=%u LIMIT 1",
			escape_data($DBS['comet'], $_POST['removeID'])
		);

		$noteR = mysqli_query($DBS['comet'], $noteQ);
		if (!$noteR) {
			printf('{ "errorMsg":"Query: %s, Error: %s" }',
				$noteQ, 
				mysqli_error($DBS['comet'])
			);
		} else {
			echo '{ "success": "success!" }';
		}
	}

?>
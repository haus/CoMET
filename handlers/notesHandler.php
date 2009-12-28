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
 * This page handles adding and updating notes.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');
	require_once('../includes/functions.php');

	if (isset($_POST['newMain']) && $_POST['newMain'] == "true") {
		// Validate the note.
		$mainNote = escapeData($DBS['comet'], $_POST['mainNote']);
	
		if (!empty($mainNote)) {
			$noteQ = sprintf("INSERT INTO notes VALUES ('%s', NULL, 0, %u, now(), %u)",
				$mainNote, $_SESSION['cardNo'], $_SESSION['userID']
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
		} else {
			echo ' { "errorMsg": "Cannot add an empty note." } ';
		}
	} elseif (isset($_POST['noteID']) && is_numeric($_POST['noteID'])) {
		$parent = (int) $_POST['noteID'];
		$note = escapeData($DBS['comet'], $_POST['note'][$parent]);
	
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
			escapeData($DBS['comet'], $_POST['removeID'])
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
	} elseif (isset($_POST['value']) && isset($_POST['id'])) {
		// Parse the threadID from the id. The id is in 'note-#' format.
		$threadArray = explode('-', $_POST['id']);
		$threadID = (int)$threadArray[1];
		
		// Check the user who wrote the note.
		$threadQ = sprintf("SELECT note, threadID, userID FROM notes WHERE threadID=%u", $threadID);
		$threadR = mysqli_query($DBS['comet'], $threadQ);
		
		if (!$threadR) printf('Query: %s, Error: %s', $threadQ, mysqli_error($DBS['comet']));
		
		list($oldValue, $threadID, $userID) = mysqli_fetch_row($threadR);
		
		// Check the level of the current user. If the user wrote the note or is of level 4 or greater, edit the note.
		if (($userID == $_SESSION['userID'] || $_SESSION['level'] >= 4) && (!empty($_POST['value'])) && ($oldValue != $_POST['value'])) {
			$updateQ = sprintf("UPDATE notes SET note = '%s', userID = %u, modified=now() WHERE threadID = %u",
				escapeData($DBS['comet'], $_POST['value']), $_SESSION['userID'], $threadID);
			$updateR = mysqli_query($DBS['comet'], $updateQ);
			
			if (!$updateR) printf('Query: %s, Error: %s', $updateQ, mysqli_error($DBS['comet']));
			else {
				echo $_POST['value'];
			}
		} else {
			echo $oldValue;
		}
		
	}
} else {
	header('Location: ../index.php');
}
?>
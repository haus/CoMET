<?php
/*
		CoMET is a stand-alone member equity tracking application designed to integrate with IS4C and Fannie.
	    Copyright (C) 2009  Matthaus Litteken

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
?>

<script type="text/javascript">
	function showRow(id) {
		$('#' + id).show();
		$('#button' + id).hide();
	}
	
	function newNote() {
		$('#newMain').val(true);
	}
	
	function addChild(id) {
		$('#noteID').val(id);
	}
</script>
<?php

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');

	$notesQ = "SELECT note, threadID, parentID, DATE(modified), DATE_FORMAT(modified, '%r'), notes.userID, u.user
		FROM notes INNER JOIN users AS u ON (notes.userID = u.userID) WHERE cardNo={$_SESSION['cardNo']}";
	$notesR = mysqli_query($DBS['comet'], $notesQ);

	echo '<h3 class="center">Notes</h3><br />
		<input type="hidden" id="newMain" name="newMain" value="false" />
		<input type="hidden" id="noteID" name="noteID" value="false" />';

	if (!$notesR) printf('Query: %s, Error: %s', $notesQ, mysqli_error($DBS['comet']));

	if (mysqli_num_rows($notesR) > 0) {
		while (list($note, $tID, $pID, $modified, $time, $uID, $name) = mysqli_fetch_row($notesR)) {
			// Build a multidimensional array.
			// Then...
			$notes[$pID][$tID] = $note;
			$details[$tID]['date'] = $modified;
			$details[$tID]['author'] = $name;
			$details[$tID]['time'] = $time;
		}
	
		printNotes($notes[0]);

	}
	echo '<table cellpadding="2" cellspacing="2" width="100%">';
	printf('<tr class="center">
			<td>
				<input type="submit" value="Add Note" name="addMainNote" onclick="%s"/>
			</td>
			<td colspan="3">
				<input type="text" name="mainNote" size="50" maxlength="100" />
			</td>
			</tr>
		</table><br />', 'newNote();'
		);
} else {
	header('Location: ../index.php');
}
	
function printNotes($parent) {
	// Need the main $tasks array:
		global $notes;
		global $details;

		// Start an ordered list:
		echo '<ul>';

		// Loop through each subarray:
		foreach ($parent as $threadID => $noteText) {

			// Display the item:
			//echo "<li>$noteText\n";
			//echo "(written by %s on %s)";
			printf('<li>
						<input type="submit" value="Reply" id="%s" name="addChild[]" onclick="%s" />
						<!--<input type="image" src="includes/images/minus-8.png" name="pmtRemove[]" onclick="%s" />-->
						%s
						<small>(written by %s on %s at %s)</small>
					<br /><p id="%u" style="display:none">
							<input type="submit" value="Add Reply" name="addNote[]" onclick="%s" />
							<input type="text" name="note[%u]" size="50" maxlength="100" />
					</p>',
					'button' . $threadID,
					'showRow(' . $threadID . ');	return false;', 
					'updateRemoveID(' . $threadID . ');', 
					$noteText . "\n",
					$details[$threadID]['author'],
					date('m/d/Y', strtotime($details[$threadID]['date'])), 
					$details[$threadID]['time'], 
					$threadID,
					'addChild(' . $threadID . ');',
					$threadID
				);
	
			// Check for subtasks:
			if (isset($notes[$threadID])) { 

				// Call this function:
				printNotes($notes[$threadID]);

			}

			// Complete the list item:
			echo '</li>';

		} // End of FOREACH loop.

		// Close the ordered list:
		echo '</ul>';
}


?>
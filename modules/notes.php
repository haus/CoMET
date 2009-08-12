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
	function updateRemoveID(id) {
		$('#removeID').val(id);
	}
	
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
require_once('../includes/config.php');
require_once('../includes/mysqli_connect.php');

$notesQ = "SELECT note, threadID, parentID, DATE(modified), notes.userID, u.user
	FROM notes INNER JOIN users AS u ON (notes.userID = u.userID) WHERE cardNo={$_SESSION['cardNo']}";
$notesR = mysqli_query($DBS['comet'], $notesQ);

echo '<h3 class="center">Notes</h3><br />
	<input type="hidden" id="removeID" name="removeID" value="false" />
	<input type="hidden" id="newMain" name="newMain" value="false" />
	<input type="hidden" id="noteID" name="noteID" value="false" />';
echo '<table cellpadding="2" cellspacing="2" width="100%">';

if (!$notesR) printf('Query: %s, Error: %s', $notesQ, mysqli_error($DBS['comet']));

if (mysqli_num_rows($notesR) > 0) {
	while (list($note, $tID, $pID, $modified, $uID, $name) = mysqli_fetch_row($notesR)) {

		printf('<tr class="center">
					<td>
						<input type="submit" value="Reply" id="%s" name="addChild[]" onclick="%s" />
						<input type="image" src="includes/images/minus-8.png" name="pmtRemove[]" onclick="%s" />
					</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
				</tr>
				<tr id="%u" style="display:none" class="center">
					<td>
						<input type="submit" value="Add Reply" name="addNote[]" onclick="%s" />
					</td>
					<td colspan="3">
						<input type="text" name="note[%u]" size="50" maxlength="100" />
					</td>
				</tr>',
				'button' . $tID,
				'showRow(' . $tID . ');	return false;', 
				'updateRemoveID(' . $tID . ');', 
				date('m-d-Y', strtotime($modified)), 
				$note, 
				$name,
				$tID,
				'addChild(' . $tID . ');',
				$tID
			);
		}
}
printf('<tr class="center">
		<td>
			<input type="submit" value="Add Note" name="addMainNote" onclick="%s"/>
		</td>
		<td colspan="3">
			<input type="text" name="mainNote" size="50" maxlength="100" />
		</td>
		</tr>
	</table><br />', 'newNote();');

?>
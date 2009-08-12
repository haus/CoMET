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
	$(document).ready(function() {
		$('#pmtDatepicker').datepicker({ dateFormat: 'yy-mm-dd' });
	});
	
	function updateRemoveID(id) {
		$('#removeID').val(id);
	}
</script>
<?php
require_once('../includes/config.php');
require_once('../includes/mysqli_connect.php');

$paymentsQ = "SELECT amount, date, memo, paymentID, reference
	FROM payments WHERE cardNo={$_SESSION['cardNo']} ORDER BY date ASC";
$paymentsR = mysqli_query($DBS['comet'], $paymentsQ);

echo '<h3 class="center">Payments</h3>
	<input type="hidden" id="removeID" name="removeID" value="false" />';
echo '<table cellpadding="2" cellspacing="2" width="100%">
	<tr><th>&nbsp;</th><th>Date</th><th>Amount</th><th>Memo</th><th>Reference</th></tr>';

if (!$paymentsR) printf('Query: %s, Error: %s', $paymentsQ, mysqli_error($DBS['comet']));

if (mysqli_num_rows($paymentsR) > 0) {
	while (list($amount, $date, $memo, $id, $ref) = mysqli_fetch_row($paymentsR)) {

		printf('<tr class="center">
				<td><input type="image" src="includes/images/minus-8.png" name="pmtRemove[]" onclick="%s" /></td>
				<td>%s</td>
				<td>$%s</td>
				<td>%s</td>
				<td>%s (Thickbox receipt link)</td>
				</tr>', 'updateRemoveID(' . $id . '); this.submit();', date('m-d-Y', strtotime($date)), number_format($amount, 2), $memo, $ref);
		}
}
echo '<tr class="center">
		<td><input type="image" src="includes/images/plus-8.png" name="pmtSubmit" id="pmtSubmit" /></td>
		<td><input type="text" name="date" id="pmtDatepicker" size="10" maxlength="10" /></td>
		<td>$<input type="text" name="amount" id="pmtAmount" size="5" maxlength="6" /></td>
		<td><input type="text" name="memo" id="pmtMemo" size="10" maxlength="50" /></td>
		<td><input type="text" name="ref" id="pmtReference" size="10" maxlength="20" /></td>
	</tr>
	</table><br />';

?>
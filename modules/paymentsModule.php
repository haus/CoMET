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

/**
 * This page loads the current payments for the current record in the payments div in the main tab.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

?>

<script type="text/JavaScript">
	function updateRemoveID(id) {
		$('#removeID').val(id);
	}

	$(document).ready(function() {
		$('#pmtDatepicker').datepicker({ dateFormat: 'mm/dd/yy', maxDate: 0, changeYear: 'true', yearRange: '2000:<?php echo date('Y') + 1; ?>' });
		$("#paymentForm :input").keypress(function (e) {
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				if ($('#changed').val() == 'true') {
					$('#navButton').val('current');
					$('#navForm').submit();
				}
				
				$('#paymentForm').submit();

				return false;
			} else {
				return true;
			}
	    });
		$('.editText').editable('./handlers/paymentHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				select: 'true',
				tooltip: 'Click to edit...',
				callback: function(value, settings) {
					$('#payments').load('./modules/paymentsModule.php');
				}
			}
		);
	});
</script>
<?php
if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');

	$paymentsQ = "SELECT amount, date, memo, paymentID, reference
		FROM payments WHERE cardNo={$_SESSION['cardNo']} ORDER BY date ASC";
	$paymentsR = mysqli_query($DBS['comet'], $paymentsQ);

	$planQ = "SELECT amount FROM paymentPlans WHERE planID=(SELECT paymentPlan FROM details WHERE cardNo={$_SESSION['cardNo']})";
	$planR = mysqli_query($DBS['comet'], $planQ);
	list($defaultAmount) = mysqli_fetch_row($planR);
	$defaultAmount = (is_null($defaultAmount) ? $_SESSION['defaultPayment'] : $defaultAmount);

	$checkQ = "SELECT SUM(p.amount), d.sharePrice
		FROM payments AS p
			RIGHT JOIN details AS d ON (d.cardNo = p.cardNo)
		WHERE d.cardNo={$_SESSION['cardNo']}
		GROUP BY d.cardNo";
	$checkR = mysqli_query($DBS['comet'], $checkQ);

	list($total, $sPrice) = mysqli_fetch_row($checkR);

	echo '<h3 class="center">Payments</h3><br />
		<input type="hidden" id="removeID" name="removeID" value="false" />' . "\n";
	echo '<table cellpadding="2" cellspacing="2" width="100%">
		<tr><th>&nbsp;</th><th>Date</th><th>Amount</th><th>Memo</th><th>Reference</th></tr>';

	if (!$paymentsR) printf('Query: %s, Error: %s', $paymentsQ, mysqli_error($DBS['comet']));

	if (mysqli_num_rows($paymentsR) > 0) {
		while (list($amount, $date, $memo, $id, $ref) = mysqli_fetch_row($paymentsR)) {

			printf('<tr class="center">
					<td><input type="image" name="pmtRemove[]" src="includes/images/minus-8.png" onclick="%s" /></td>
					<td>%s</td>
					<td>$%s</td>
					<td><span class="editText" id="memo-%u">%s</span></td>
					<td><span class="editText" id="reference-%u">%s</span></td>
					</tr>', 
					'updateRemoveID(' . $id . ');', 
					date('m/d/Y', 
					strtotime($date)), 
					number_format($amount, 2), 
					$id,
					(empty($memo) ? '(No Memo)' : $memo), 
					$id,
					(empty($ref) ? '(No Reference)' : $ref)
				);
		}
	}

	if (($total != $sPrice) || (is_null($total) || is_null($sPrice))) {
		echo '<tr class="center">
				<td><input type="image" src="includes/images/plus-8.png" name="pmtSubmit" id="pmtSubmit" /></td>
				<td><input type="text" name="date" id="pmtDatepicker" size="10" maxlength="10" /></td>
				<td>$<input type="text" name="amount" id="pmtAmount" size="5" maxlength="7" value="' . number_format($defaultAmount, 2) . '" /></td>
				<td><input type="text" name="memo" id="pmtMemo" size="35" maxlength="50" /></td>
				<td><input type="text" name="ref" id="pmtReference" size="10" maxlength="20" /></td>
			</tr>
			</table><br />';
	} else {
		echo '</table><br /><input type="hidden" id="pmtDatepicker" value="" name="date" />';
	}
} else {
	header('Location: ../index.php');
}
?>

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
	$.editable.addInputType("datepicker", {
		element:  function(settings, original) {
			var input = $("<input type=\"text\" name=\"value\" />");
			$(this).append(input);
			return(input);
		},
		plugin:  function(settings, original) {
			var form = this;
			$("input", this).filter(":text").datepicker({
				onSelect: function(dateText) { $(this).hide(); $(form).trigger("submit"); },
				changeYear: 'true',
				yearRange: '2000:<?php echo date('Y') + 1; ?>'
			});
		}
	});

	$(document).ready(function() {
		$('#editPrice').editable('./handlers/summaryHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				select: 'true',
				tooltip: 'Click to edit...',
				callback: reload
			}
		);

		$('#editPlan').editable('./handlers/summaryHandler.php',
			{
				loadurl: './handlers/summaryHandler.php?plans=true',
				type: 'select',
				style: 'display: inline',
				onblur: 'submit',
				callback: reload
			}
		);

		$('.editDate').editable('./handlers/summaryHandler.php',
			{
				type: 'datepicker',
				tooltip: 'Click to edit...',
				cancel: 'Cancel',
				width: '100px',
				onblur: 'ignore',
				callback: reload
			}
		);
	});
</script>

<?php
if (isset($_SESSION['level'])) {

	require_once('../includes/config.php');

	$payQ = "SELECT SUM(p.amount), MAX(date), d.nextPayment, d.joined, d.sharePrice, d.paymentPlan, pp.frequency, pp.amount, d.startDate, u.user
		FROM payments AS p
			RIGHT JOIN details AS d ON (d.cardNo = p.cardNo)
			INNER JOIN paymentPlans AS pp ON (d.paymentPlan = pp.planID)
			INNER JOIN users AS u ON (d.userID = u.userID)
		WHERE d.cardNo={$_SESSION['cardNo']}
		GROUP BY d.cardNo";
	$payR = mysqli_query($DBS['comet'], $payQ);

	// TODO: Check for rows, if 0 display more obvious form elements.

	if (!$payR) printf('Query: %s, Error: %s', $payQ, mysqli_error($DBS['comet']));
	list($paid, $lastPaid, $nextPayment, $joinDate, $sharePrice, $pmtPlan, $freq, $amount, $modified, $user) = mysqli_fetch_row($payR);

	if (!is_null($joinDate)) {
		$plan = ($pmtPlan > 0 ?
					($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
					: "$45 annually");

		printf('<p>
					<strong>Card No: </strong>%u<br />
					<strong>Join Date: </strong><span name="joinDate" class="editDate" id="editJoined">%s</span><br />
					<strong>Share Price: </strong>$<span name="sharePrice" id="editPrice">%s</span><br />
					<strong>Total Paid: </strong>$%s<br />
					<strong>Remaining To Pay: </strong>$%s<br />
					<strong>Next Payment Due: </strong>%s<br />
					<strong>Last Payment Made: </strong>%s<br />
					<strong>Payment Plan: </strong><span name="paymentPlan" id="editPlan">%s</span><br />
					<strong>Last Modified By: </strong>%s on %s
				</p>',
				$_SESSION['cardNo'],
				(is_null($joinDate) ? $joinDate : date('m/d/Y', strtotime($joinDate))),
				number_format((is_null($sharePrice) ? $_SESSION['sharePrice'] : $sharePrice), 2),
				number_format($paid,2),
				number_format((is_null($sharePrice) ? $_SESSION['sharePrice'] : $sharePrice)-$paid,2),
				(is_null($nextPayment) ?
					($paid == $sharePrice ? 'Paid off' : $nextPayment) :
					'<span name="nextDue" class="editDate" id="editNext">' . date('m/d/Y', strtotime($nextPayment)) . '</span>'),
				(is_null($lastPaid) ? $lastPaid : date('m/d/Y', strtotime($lastPaid))),
				$plan,
				$user,
				date('m/d/Y', strtotime($modified))
				);
	} else {
?>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#datepicker').datepicker({ dateFormat: 'yy-mm-dd', maxDate: 0 });
			});
		</script>
<?php
		$planQ = "SELECT planID, frequency, amount
			FROM paymentPlans
			ORDER BY planID";
		$planR = mysqli_query($DBS['comet'], $planQ);

		$plan = '<select name="plan">';

		while (list($planID, $freq, $amount) = mysqli_fetch_row($planR)) {
			$plan .= sprintf('<option value="%s">%s</option>',
					$planID,
					($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
				);
		}

		$plan .= "</select>";

		printf('<p>
					<strong>Card No: </strong>%u<br />
					<strong>Join Date: </strong><input type="text" name="joinDate" id="datepicker" size="10" maxlength="10" value="%s" /><br />
					<strong>Share Price: </strong>$<input type="text" name="sharePrice" size="7" maxlength="7" value="%s" /><br />
					<strong>Total Paid: </strong>$0<br />
					<strong>Remaining To Pay: </strong>$%s<br />
					<strong>Next Payment Due: </strong>%s<br />
					<strong>Last Payment Made: </strong>%s<br />
					<strong>Payment Plan: </strong>%s
				</p>',
				$_SESSION['cardNo'],
				date('m/d/Y'),
				number_format($_SESSION['sharePrice'], 2),
				number_format($_SESSION['sharePrice'], 2),
				'N/A',
				'N/A',
				$plan
				);
	}
} else {
	header('Location: ../index.php');
}
?>

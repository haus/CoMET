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
		$('#editPrice').editable('./handlers/summaryHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);

		$('#editPlan').editable('./handlers/summaryHandler.php', 
			{
				loadurl: './handlers/summaryHandler.php?plans=true',
				type: 'select',
				style: 'display: inline',
				onblur: 'submit'
			}
		);
	});
</script>

<?php
if (isset($_SESSION['level'])) {

	require_once('../includes/config.php');
	require_once('../includes/mysqli_connect.php');

	$payQ = "SELECT SUM(p.amount), MAX(date), d.nextPayment, d.joined, d.sharePrice, d.paymentPlan, pp.frequency, pp.amount 
		FROM payments AS p 
			RIGHT JOIN details AS d ON (d.cardNo = p.cardNo) 
			INNER JOIN paymentPlans AS pp ON (d.paymentPlan = pp.planID)
		WHERE d.cardNo={$_SESSION['cardNo']}";
	$payR = mysqli_query($DBS['comet'], $payQ);

	if (!$payR) printf('Query: %s, Error: %s', $payQ, mysqli_error($DBS['comet']));
	list($paid, $lastPaid, $nextPayment, $joinDate, $sharePrice, $pmtPlan, $freq, $amount) = mysqli_fetch_row($payR);

	$plan = ($pmtPlan > 0 ? 
				($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
				: "$45 annually");

	printf('<p>
				<strong>Card No: </strong>%u<br />
				<strong>Join Date: </strong>%s<br />
				<strong>Share Price: </strong>$<span name="sharePrice" id="editPrice">%s</span><br />
				<strong>Total Paid: </strong>$%s<br />
				<strong>Remaining To Pay: </strong>$%s<br />
				<strong>Next Payment Due: </strong>%s<br />
				<strong>Last Payment Made: </strong>%s<br />
				<strong>Payment Plan: </strong><span name="paymentPlan" id="editPlan">%s</span>
			</p>', 
			$_SESSION['cardNo'], 
			(is_null($joinDate) ? $joinDate : date('m-d-Y', strtotime($joinDate))), 
			number_format((is_null($sharePrice) ? $_SESSION['sharePrice'] : $sharePrice), 2),
			number_format($paid,2), 
			number_format($_SESSION['sharePrice']-$paid,2), 
			(is_null($nextPayment) ? $nextPayment : date('m-d-Y', strtotime($nextPayment))), 
			(is_null($lastPaid) ? $lastPaid : date('m-d-Y', strtotime($lastPaid))),
			$plan
			);
} else {
	header('Location: ../index.php');
}
?>
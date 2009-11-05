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

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');
	require_once('../includes/mysqli_connect.php');

	$memTypeQ = "SELECT memType, CONCAT(SUBSTR(memdesc, 1, 1), LOWER(SUBSTR(memdesc, 2, LENGTH(memdesc)))) FROM memtype ORDER BY memType ASC";
	$memTypeR = mysqli_query($DBS['is4c_op'], $memTypeQ);

	$staffQ = "SELECT staff_no, CONCAT(SUBSTR(staff_desc, 1, 1), LOWER(SUBSTR(staff_desc, 2, LENGTH(staff_desc)))) FROM staff ORDER BY staff_no ASC";
	$staffR = mysqli_query($DBS['is4c_op'], $staffQ);

	while (list($num, $desc) = mysqli_fetch_row($memTypeR)) {
		$memType[$num] = $desc;
	}

	while (list($num, $desc) = mysqli_fetch_row($staffR)) {
		$staff[$num] = $desc;
	}

	/**
	 * Owner module for CoMET. Will interface with IS4C/Fannie database to retrieve
	 * data and update members.
	 *
	 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
	 * @version 0.1
	 * @package CoMET
	 */
	?>
	<script src="../includes/javascript/jquery-1.3.2.min.js" type="text/javascript"></script>

	<?php
	echo '<div id="ownerForm">';

		for ($i = 0; $i <= $_SESSION['houseHoldSize']; $i++) {
			if ($i == 0) {
				echo '<span class="person">Person #</span>
				<span class="text">First Name</span>
				<span class="text">Last Name</span>
				<span class="widedropdown">Member Type</span>
				<span class="widedropdown">Staff Level</span>
				<span class="narrowdropdown">Discount</span>
				<span class="check">Write Checks?</span>
				<span class="check charge">House Charge?</span>';
			} else {
				$memQ = "SELECT * FROM owners WHERE cardNo = {$_SESSION['cardNo']} AND personNum = $i LIMIT 1";
				$memR = mysqli_query($DBS['comet'], $memQ);

				if (!$memR) printf('Query: %s, Error: %s', $memQ, mysqli_error($DBS['comet']));
				else $memRow = mysqli_fetch_array($memR, MYSQLI_ASSOC);

				printf("\n" . '<span class="person newline">%u</span>
					<span class="text">
						<input type="text" id="first(%u)" name="first[%u]" maxlength="50" size="20" value="%s" />
					</span>
					<span class="text">
						<input type="text" id="last(%u)" name="last[%u]" maxlength="50" size="20" value="%s" />
					</span>
					<span class="widedropdown">
						<select id="memType(%u)" name="memType[%u]" onChange="updateDiscount(%u);">',
						$i, $i, $i, $memRow['firstName'], $i, $i, $memRow['lastName'], $i, $i, $i);
				foreach ($memType AS $num => $desc)
					printf('<option value="%u"%s>%s</option>', $num, ($memRow['memType'] == $num ? ' selected="selected"' : ''), $desc);
				printf('</select>
					</span>
					<span class="widedropdown">
						<select id="staff(%u)" class="staffDrop" name="staff[%u]" onChange="updateCharge(this, %u);updateDiscount(%u);">', $i, $i, $i, $i);
				foreach ($staff AS $num => $desc)
					printf('<option value="%u"%s>%s</option>', $num, ($memRow['staff'] == $num ? ' selected="selected"' : ''), $desc);
				printf('</select>
					</span>
					<span class="narrowdropdown">
						<select id="discount(%u)" name="discount[%u]">', $i, $i);
				foreach ($_SESSION['discounts'] AS $disc)
							printf('<option value="%u"%s>%u%%</option>', $disc, ($memRow['discount'] == $disc ? ' selected="selected"' : ''), $disc);
				printf('</select>
					</span>
					<span class="check">
						<input type="checkbox" id="checks(%u)" name="checks[%u]"%s />
					</span>
					<span class="check">
						<input type="checkbox" id="charge(%u)" class="charge chargeCheck" disabled="true" name="charge[%u]"%s />
					</span>',
					$i, $i, ($memRow['writeChecks'] == 1 ? ' checked="checked"' : ''), $i, $i, ($memRow['chargeOk'] == 1 ? ' checked="checked"' : ''));
			}
		}
	echo '</div>';
} else {
	header('Location: ../index.php');
}
?>
<script type="text/javascript">
// This script auto populates discount, and disables house charge appropriately for ACG. Insert store-specific values as needed.

$('#ownerForm').ready(function() {
	$('#ownerForm :input').change(function() {
		triggerChange()
	});
	staff = $('.staffDrop');
	charge = $('.chargeCheck');
	$('.charge').disabled = true;
	for (var i = 0; i < staff.length; i++) {
		if (staff[i].value == 1 || staff[i].value == 2 || staff[i].value == 5) {
			charge[i].disabled = false;
		}
	}

});

function updateCharge(select, person) {
	charge = document.getElementById('charge(' + person + ')');
	if (select.value == 1 || select.value == 2 || select.value == 5) {
		charge.disabled = false;
	} else {
		charge.disabled = true;
		charge.checked = false;
	}
}

function updateDiscount(person) {
	memtype = document.getElementById('memType(' + person + ')');
	staff = document.getElementById('staff(' + person + ')');
	discount = document.getElementById('discount(' + person + ')');

	// Big if. ACG specific. (0,2,5,15)
	if (staff.value == 1 || staff.value == 4 || staff.value == 5) {
		discount.selectedIndex = 3;
	} else if (staff.value == 6) {
		discount.selectedIndex = 0;
	} else if (staff.value == 0 || staff.value == 2 || staff.value == 3) {
		if (memtype.value == 1 || memtype.value == 2 || memtype.value == 6) {
			discount.selectedIndex = 1;
		} else {
			discount.selectedIndex = 0;
		}
	} else {
		discount.selectedIndex = 0;
	}
}

</script>

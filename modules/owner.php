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

$memTypeQ = "SELECT memType, CONCAT(SUBSTR(memdesc, 1, 1), LOWER(SUBSTR(memdesc, 2, LENGTH(memdesc)))) FROM memtype ORDER BY memType ASC";
$memTypeR = mysqli_query($DBS['is4c_op'], $memTypeQ);

$staffQ = "SELECT staff, CONCAT(SUBSTR(staffDesc, 1, 1), LOWER(SUBSTR(staffDesc, 2, LENGTH(staffDesc)))) FROM staff ORDER BY staff ASC";
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
			<span class="checkbox">Write Checks?</span>
			<span class="checkbox">House Charge?</span>';
		} else {
			printf("\n" . '<span class="person newline">%u</span>
				<span class="text">
					<input type="text" name="first[%u]" maxlength="50" size="20" value="First Name" />
				</span>
				<span class="text">
					<input type="text" name="last[%u]" maxlength="50" size="20" value="Last Name" />
				</span>
				<span class="widedropdown">
					<select name="memType[%u]">', $i, $i, $i, $i);
			foreach ($memType AS $num => $desc)
				printf('<option value="%u">%s</option>', $num, $desc);
			printf('</select>
				</span>
				<span class="widedropdown">
					<select name="staff">');
			foreach ($staff AS $num => $desc)
				printf('<option value="%u">%s</option>', $num, $desc);
			printf('</select>
				</span>
				<span class="narrowdropdown">
					<select name="discount[%u]">', $i);
			foreach ($_SESSION['discounts'] AS $disc)
						printf('<option value="%u">%u%%</option>', $disc, $disc);
			printf('</select>
				</span>
				<span class="checkbox">
					<input type="checkbox" name="checks[%u]" checked="checked" />
				</span>
				<span class="checkbox">
					<input type="checkbox" name="charge[%u]" checked="checked" />
				</span>', $i, $i);
		}
	}
echo '</div>';

?>
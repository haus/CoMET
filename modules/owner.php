<?php
// require_once('../includes/config.php');
// require_once('../includes/mysqli_connect.php');

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
echo '<fieldset id="ownerForm">';

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
echo '</fieldset>';

?>
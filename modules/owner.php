<?php
/**
 * @TODO Get this stuff into config.ini
 */
$_SESSION['houseHoldSize'] = 2;
$_SESSION['discounts'] = array(0,2,5,15);
$_SESSION['is4c'] = array('host' => 'localhost', 'user' => 'root', 'password' => 'lemoncoke', 'database' => 'is4c_op');
$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet');

$DBS['is4c'] = mysqli_connect($_SESSION['is4c']['host'], $_SESSION['is4c']['user'], $_SESSION['is4c']['password'], $_SESSION['is4c']['database']) or die('fail!');
$DBS['comet'] = mysqli_connect($_SESSION['DB']['host'], $_SESSION['DB']['user'], $_SESSION['DB']['password'], $_SESSION['DB']['database']);

$memTypeQ = "SELECT memType, CONCAT(SUBSTR(memdesc, 1, 1), LOWER(SUBSTR(memdesc, 2, LENGTH(memdesc)))) FROM memtype ORDER BY memType ASC";
$memTypeR = mysqli_query($DBS['is4c'], $memTypeQ);

while (list($num, $desc) = mysqli_fetch_row($memTypeR)) {
	$memType[$num] = $desc;
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
<style>
	.form {
		width:1000px;
	}

	.person {
		width:7.5%;
		float:left;
		text-align:center;
		font-weight:bold;
	}

	.text {
		width:17.5%;
		float:left;
		text-align:center;
		font-weight:bold;
	}

	.widedropdown {
		width:17.5%;
		float:left;
		text-align:center;
		font-weight:bold;
	}

	.narrowdropdown {
		width:7.5%;
		float:left;
		text-align:center;
		font-weight:bold;
	}

	.checkbox {
		width:7.5%;
		float:left;
		text-align:center;
		font-weight:bold;
	}

	.newline {
		clear:left;
	}

</style>
<?php
echo '<fieldset class="form">';

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
					<select name="staff">
						<option value="0">Staff Level</option>
					</select>
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
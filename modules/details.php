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

/**
 * This page loads the current details for the current record in the details div in the main tab.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

if (isset($_SESSION['level'])) {
	require_once('../includes/config.php');

	$state_list = array('AL'=>"Alabama",
	                'AK'=>"Alaska", 
	                'AZ'=>"Arizona", 
	                'AR'=>"Arkansas", 
	                'CA'=>"California", 
	                'CO'=>"Colorado", 
	                'CT'=>"Connecticut", 
	                'DE'=>"Delaware", 
	                'DC'=>"District Of Columbia", 
	                'FL'=>"Florida", 
	                'GA'=>"Georgia", 
	                'HI'=>"Hawaii", 
	                'ID'=>"Idaho", 
	                'IL'=>"Illinois", 
	                'IN'=>"Indiana", 
	                'IA'=>"Iowa", 
	                'KS'=>"Kansas", 
	                'KY'=>"Kentucky", 
	                'LA'=>"Louisiana", 
	                'ME'=>"Maine", 
	                'MD'=>"Maryland", 
	                'MA'=>"Massachusetts", 
	                'MI'=>"Michigan", 
	                'MN'=>"Minnesota", 
	                'MS'=>"Mississippi", 
	                'MO'=>"Missouri", 
	                'MT'=>"Montana",
	                'NE'=>"Nebraska",
	                'NV'=>"Nevada",
	                'NH'=>"New Hampshire",
	                'NJ'=>"New Jersey",
	                'NM'=>"New Mexico",
	                'NY'=>"New York",
	                'NC'=>"North Carolina",
	                'ND'=>"North Dakota",
	                'OH'=>"Ohio", 
	                'OK'=>"Oklahoma", 
	                'OR'=>"Oregon", 
	                'PA'=>"Pennsylvania", 
	                'RI'=>"Rhode Island", 
	                'SC'=>"South Carolina", 
	                'SD'=>"South Dakota",
	                'TN'=>"Tennessee", 
	                'TX'=>"Texas", 
	                'UT'=>"Utah", 
	                'VT'=>"Vermont", 
	                'VA'=>"Virginia", 
	                'WA'=>"Washington", 
	                'WV'=>"West Virginia", 
	                'WI'=>"Wisconsin", 
	                'WY'=>"Wyoming");

	/**
	 * Details module for CoMET. Will interface with CoMET database to retrieve
	 * data and update members.
	 *
	 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
	 * @version 0.1
	 * @package CoMET
	 */
	?>
	<script src="../includes/javascript/jquery.min.js" type="text/javascript"></script>
	<script src="./includes/javascript/jquery.maskedinput.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function() 
			{
		   		$("#phone").mask("(999) 999-9999");
				$("#zip").mask("99999?-9999");
				$('#detailsForm :input').change(function() {
					triggerChange();
					});
				$('#detailsForm :checkbox').click(function() {
					triggerChange();
					});
			}
		);
	</script>
	<?php
	$memDetailsQ = "SELECT address, phone, city, state, zip, phone, email, noMail
	 	FROM details WHERE cardNo = {$_SESSION['cardNo']} LIMIT 1";
	$memDetailsR = mysqli_query($DBS['comet'], $memDetailsQ);

	if (!$memDetailsR) {
		printf('Query: %s, Error: %s', $memDetailsQ, mysqli_error($DBS['comet']));
	} else {
	//	echo '<script type="text/javascript">alert(' . "'hi'" . ');</script>';
		list($address, $phone, $city, $state, $zip, $phone, $email, $noMail) = mysqli_fetch_row($memDetailsR);
	}
	
	$state = (is_null($state) ? $_SESSION['defaultState'] : $state);

	echo '<div id="detailsForm">';
	printf('<span class="address">
			<label for="address">Address </label>
			<textarea name="address" rows="2" cols="60">%s</textarea>
		</span>
		<span class="check">
			<label for="noMail">No Mail <input type="checkbox" name="noMail" %s /></label>
		</span>
		<span class="city newline">
			<label for="city">City </label>
			<input type="text" name="city" size="20" maxlength="50" value="%s" />
		</span>
		<span class="state">
			<label for="state">State </label>
			<select name="state" id="state">', $address, ($noMail == 1 ? 'checked="checked"' : NULL), $city);
	foreach ($state_list AS $abrev => $name)
		printf('<option value="%s"%s>%s</option>' . "\n", $abrev, ($abrev == $state ? ' selected="selected"' : ''), $name);
	printf('</select>
		</span>
		<span class="zip">
			<label for="zip">Zip Code </label>
			<input type="text" name="zip" id="zip" size="10" maxlength="10" value="%s" />
		</span>
		<span class="phone newline">
			<label for="phone">Phone </label>
			<input type="text" name="phone" id="phone" size="15" maxlength="15" value="%s" />
		</span>
		<span class="email">
			<label for="email">Email Address </label>
			<input type="text" name="email" size="30" maxlength="50" value="%s" />
		</span>
		</div>', $zip, $phone, $email);
} else {
	header('Location: ../index.php');
}
?>
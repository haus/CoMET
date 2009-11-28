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
?>

<script type="text/javascript">
	$.editable.addInputType('password', {
	    element : function(settings, original) {
	        var input = $('<input type="password">');
	        $(this).append(input);
	        return(input);
	    }
	});

	$(document).ready(function() {
		$('.editText').editable('./handlers/configHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	
		$('.editArea').editable('./handlers/configHandler.php',
			{
				type: 'textarea',
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	
		$('.editPass').editable('./handlers/configHandler.php',
			{
				type: 'password',
				data: function(value, settings) {
				      /* Convert password to empty. */
				      return '';
				    },
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
		
		$('.editPlan').editable('./handlers/configHandler.php', 
			{ 
				loadurl : './handlers/configHandler.php?json=defaultPlan',
				type   : 'select',
				onblur : 'submit',
				style: 'display: inline',
				tooltip: 'Click to edit...'
	 		});

		$('.editDiscount').editable('./handlers/configHandler.php',
			{ 
				loadurl : './handlers/configHandler.php?json=defaultDiscount',
				type   : 'select',
				onblur : 'submit',
				style: 'display: inline',
				tooltip: 'Click to edit...'
	 		});
		
		$('.editStaff').editable('./handlers/configHandler.php', 
			{ 
				loadurl : './handlers/configHandler.php?json=defaultStaff',
				type   : 'select',
				onblur : 'submit',
				style: 'display: inline',
				tooltip: 'Click to edit...'
	 		});

		$('.editMemType').editable('./handlers/configHandler.php',
			{ 
				loadurl : './handlers/configHandler.php?json=defaultMemType',
				type   : 'select',
				onblur : 'submit',
				style: 'display: inline',
				tooltip: 'Click to edit...'
	 		});
		
		$('.editState').editable('./handlers/configHandler.php',
			{ 
				loadurl : './handlers/configHandler.php?json=defaultState',
				type   : 'select',
				onblur : 'submit',
				style: 'display: inline',
				tooltip: 'Click to edit...'
	 		});
		
		var configOptions = { 
		       //target:        '#output1',   // target element(s) to be updated with server response 
		       beforeSubmit:  validateConfig, // pre-submit callback 
		       success:       configResponse,  // post-submit callback 

		       // other available options: 
		       //url:       './modules/handler.php'         // override for form's 'action' attribute 
		       //type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
		       dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
		       //clearForm: true        // clear all form fields after successful submit 
		       //resetForm: true        // reset the form after successful submit 

		       // $.ajax options can be used here too, for example: 
		       //timeout:   3000 
		   };

		// bind to the form's submit event 
		   $('#configForm').submit(function() {

			$(this).ajaxSubmit(configOptions);
			return false;
		});

		$('#configForm :button').click(function() {
			$('#testType').val(this.id);
		});
	});
</script>
<form id="configForm" method="POST" name="configForm" action="./handlers/configHandler.php">
<?php
$configQ = "SELECT name, value 
	FROM options 
	WHERE name IN 
		('smtpHost', 
			'smtpUser', 'smtpPass',
			'systemUser', 'systemPass',
			'opHost', 'opUser', 'opPass', 'opDB', 
			'logHost', 'logUser', 'logPass', 'logDB',
			'houseHoldSize', 'discounts', 'sharePrice', 'defaultPayment', 'defaultPlan', 
			'defaultStaff', 'defaultMemType', 'defaultDiscount', 'defaultState', 'syncURL')";
$configR = mysqli_query($DBS['comet'], $configQ);
if (!$configR) {
	printf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $configQ);
	exit();
}

while (list($name, $value) = mysqli_fetch_row($configR)) {
	$config[$name] = $value;
}

$memTypeQ = "SELECT memType, CONCAT(SUBSTR(memdesc, 1, 1), LOWER(SUBSTR(memdesc, 2, LENGTH(memdesc)))) FROM memtype ORDER BY memType ASC";
$memTypeR = mysqli_query($DBS['is4c_op'], $memTypeQ);

$staffQ = "SELECT staff_no, CONCAT(SUBSTR(staff_desc, 1, 1), LOWER(SUBSTR(staff_desc, 2, LENGTH(staff_desc)))) FROM staff ORDER BY staff_no ASC";
$staffR = mysqli_query($DBS['is4c_op'], $staffQ);

while (list($num, $desc) = mysqli_fetch_row($memTypeR)) {
	$memType[$num] = $desc;
}

while (list($num, $desc) = mysqli_fetch_row($staffR)) {
	$staffList[$num] = $desc;
}

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

// SMTP Settings...
echo '<h3>SMTP Settings</h3>';
echo '<h5>Host Settings</h5>';
printf('<p><strong>SMTP Host: </strong><span class="editText" id="smtpHost">%s</span></p><br />', $config['smtpHost']);

echo '<h5>Reminder E-mail Account Settings</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="smtpUser">%s</span>
	<strong>Password: </strong><span class="editPass" id="smtpPass">%s</span>

</p>', $config['smtpUser'], '(hidden)');
echo '<p id="smtpResponse">&nbsp;</p>
	<button type="submit" id="smtpTest" name="smtpTest">Test Reminder Email Account</button><br /><br />';
	
echo '<h5>System E-mail Account Settings</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="systemUser">%s</span>
	<strong>Password: </strong><span class="editPass" id="systemPass">%s</span>
</p>', $config['systemUser'], '(hidden)');
echo '<p id="systemResponse">&nbsp;</p>
	<button type="submit" id="systemTest" name="systemTest">Test System Email Account</button><br /><br />';

$planQ = "SELECT * FROM paymentPlans WHERE planID={$config['defaultPlan']}";
$planR = mysqli_query($DBS['comet'], $planQ);
if (!$planR) printf('Query: %s, Error: %s', $planQ, mysqli_error($DBS['comet']));

list($planID, $freq, $amount) = mysqli_fetch_row($planR);
$config['defaultPlan'] = sprintf('%s',
	($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
);

// Store specific settings...
echo '<h3>Store Specific Settings</h3>';
printf('<p>
		<strong>Max Household Size: </strong><span class="editText" id="houseHoldSize">%s</span> 
		<strong>Allowed Discounts: </strong><span class="editText" id="discounts">%s</span>
		<strong>Default Share Price: </strong>$<span class="editText" id="sharePrice">%s</span>
		<strong>Default Payment Amount: </strong>$<span class="editText" id="defaultPayment">%s</span>
		<strong>Default Payment Plan: </strong><span class="editPlan" id="defaultPlan">%s</span>
	</p>
	<p>
		<strong>Default Staff: </strong><span class="editStaff" id="defaultStaff">%s</span>
		<strong>Default Memtype: </strong><span class="editMemType" id="defaultMemType">%s</span>
		<strong>Default Discount: </strong><span class="editDiscount" id="defaultDiscount">%s</span>%%
		<strong>Default State: </strong><span class="editState" id="defaultState">%s</span>
	</p>
	<p>
		<strong>Fannie Sync URL: </strong><span class="editText" id="syncURL">%s</span>
	</p>
	<br />', 
	$config['houseHoldSize'], $config['discounts'], $config['sharePrice'], $config['defaultPayment'], 
	$config['defaultPlan'], $staffList[$config['defaultStaff']], $memType[$config['defaultMemType']], $config['defaultDiscount'], 
	$state_list[$config['defaultState']], $config['syncURL']);


// IS4C Connection Information...
echo '<h3>IS4C Connection Information</h3>';

echo '<h5>IS4C_OP Connection Information</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="opUser">%s</span> 
	<strong>Password: </strong><span class="editPass" id="opPass">%s</span>
	<strong>Host: </strong><span class="editText" id="opHost">%s</span>
	<strong>DB Name: </strong><span class="editText" id="opDB">%s</span>
</p>', $config['opUser'], '(hidden)', $config['opHost'], $config['opDB']);
echo '<p id="opResponse">&nbsp;</p>
	<button type="submit" id="opTest" name="opTest">Test OP DB Connection</button><br /><br />';

echo '<h5>IS4C_LOG Connection Information</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="logUser">%s</span> 
	<strong>Password: </strong><span class="editPass" id="logPass">%s</span>
	<strong>Host: </strong><span class="editText" id="logHost">%s</span>
	<strong>DB Name: </strong><span class="editText" id="logDB">%s</span>
</p>', $config['logUser'], '(hidden)', $config['logHost'], $config['logDB']);
echo '<p id="logResponse">&nbsp;</p>
<button type="submit" id="logTest" name="logTest">Test LOG DB Connection</button>';
?>

<input type="hidden" id="testType" name="testType" value="" />
<input type="hidden" name="submitted" value="TRUE" />
</form>
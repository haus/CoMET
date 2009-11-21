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
	});
</script>

<?php
$configQ = "SELECT name, value 
	FROM options 
	WHERE name IN 
		('smtpUser', 'smtpPass', 'smtpHost', 
			'opHost', 'opUser', 'opPass', 'opDB', 
			'logHost', 'logUser', 'logPass', 'logDB',
			'houseHoldSize', 'discounts', 'sharePrice', 'defaultPayment', 'defaultPlan')";
$configR = mysqli_query($DBS['comet'], $configQ);
if (!$configR) {
	printf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $configQ);
	exit();
}

while (list($name, $value) = mysqli_fetch_row($configR)) {
	$config[$name] = $value;
}
// SMTP Settings...
echo '<h3>SMTP Settings</h3>';
printf('<p>
	<strong>User: </strong><span class="editText" id="smtpUser">%s</span>
	<strong>Password: </strong><span class="editPass" id="smtpPass">%s</span>
	<strong>SMTP Host: </strong><span class="editText" id="smtpHost">%s</span>
</p><br />', $config['smtpUser'], '(hidden)', $config['smtpHost']);

// Store specific settings...
echo '<h3>Store Specific Settings</h3>';
printf('<p>
	<strong>Max Household Size: </strong><span class="editText" id="houseHoldSize">%s</span> 
	<strong>Allowed Discounts: </strong><span class="editText" id="discounts">%s</span>
	<strong>Default Share Price: </strong><span class="editText" id="sharePrice">%s</span>
	<strong>Default Payment Amount: </strong><span class="editText" id="defaultPayment">%s</span>
	<strong>Default Payment Plan: </strong><span class="editText" id="defaultPlan">%s</span>
</p><br />', $config['houseHoldSize'], $config['discounts'], $config['sharePrice'], $config['defaultPayment'], $config['defaultPlan']);


// IS4C Connection Information...
echo '<h3>IS4C Connection Information</h3>';

echo '<h5>IS4C_OP Connection Information</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="opUser">%s</span> 
	<strong>Password: </strong><span class="editPass" id="opPass">%s</span>
	<strong>Host: </strong><span class="editText" id="opHost">%s</span>
	<strong>DB Name: </strong><span class="editText" id="opDB">%s</span>
</p><br />', $config['opUser'], '(hidden)', $config['opHost'], $config['opDB']);

echo '<h5>IS4C_LOG Connection Information</h5>';
printf('<p>
	<strong>User: </strong><span class="editText" id="logUser">%s</span> 
	<strong>Password: </strong><span class="editPass" id="logPass">%s</span>
	<strong>Host: </strong><span class="editText" id="logHost">%s</span>
	<strong>DB Name: </strong><span class="editText" id="logDB">%s</span>
</p><br />', $config['logUser'], '(hidden)', $config['logHost'], $config['logDB']);


echo '<button type="submit" name="mailerTest">Test SMTP</button>';
?>
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
	$(document).ready(function() {
		$('.editText').editable('./handlers/mailerHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	
		$('.editArea').editable('./handlers/mailerHandler.php',
			{
				type: 'autogrow',
				onblur: 'submit',
				tooltip: 'Click to edit...',
				data: function(value, settings) {
				      /* Convert <br> to nothing. */
				      var retval = value.replace(/<br[\s\/]?>/gi, '');
				      return retval;
				    },
				autogrow: {
				        lineHeight : 16,
				        maxHeight  : 512
				}
			}
		);
	});
</script>

<h1>Auto-reminder Mailer Settings</h1>
<h4>Available tags to use: <em>[firstName], [lastName], [balance], [sharePrice], [dueDate], [paymentPlan]</em></h4>

<?php
echo '<br />';

$mailer = array();

$comingDueQ = "SELECT name, value 
	FROM options 
	WHERE name IN 
		('comingDueDays', 'comingDueMsg', 'pastDueDays', 'pastDueMsg', 'inactiveDays', 'inactiveMsg', 'reminderEmail', 'reminderFrom')";
$comingDueR = mysqli_query($DBS['comet'], $comingDueQ);
while (list($name, $value) = mysqli_fetch_row($comingDueR)) {
	$value = str_replace ('\r', '', $value);
	$value = nl2br($value);
	$mailer[$name] = $value;
}

printf('<h3>Reminder Email Address: <span class="editText" id="reminderFrom">%s</span>&lt;<span class="editText" id="reminderEmail">%s</span>&gt;)</h3><br />',
	$mailer['reminderFrom'], $mailer['reminderEmail']);

printf('<h3>Coming Due Message (# of days to message: <span class="editText" id="comingDueDays">%s</span>)</h3>
<span class="editArea" id="comingDueMsg">%s</span><br /><br />', $mailer['comingDueDays'], $mailer['comingDueMsg']);

printf('<h3>Past Due Message (# of days to message: <span class="editText" id="pastDueDays">%s</span>)</h3>
<span class="editArea" id="pastDueMsg">%s</span><br /><br />', $mailer['pastDueDays'], $mailer['pastDueMsg']);

printf('<h3>Inactive Message (# of days to message: <span class="editText" id="inactiveDays">%s</span>)</h3>
<span class="editArea" id="inactiveMsg">%s</span><br /><br />', $mailer['inactiveDays'], $mailer['inactiveMsg']);

?>
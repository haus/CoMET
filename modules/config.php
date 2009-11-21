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
		$('.editText').editable('./handlers/mailerHandler.php',
			{
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	
		$('.editArea').editable('./handlers/mailerHandler.php',
			{
				type: 'textarea',
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	
		$('.editPass').editable('./handlers/mailerHandler.php',
			{
				type: 'password',
				style: 'display: inline',
				onblur: 'submit',
				tooltip: 'Click to edit...'
			}
		);
	});
</script>

'smtpUser', 'smtpPass', 'smtpHost'

echo '<h3>SMTP Settings</h3>';
printf('<p>
	<strong>User: </strong><span class="editText" id="stmpUser">%s</span> 
	<strong>Password: </strong><span class="editPass" id="smtpPass">%s</span>
	<strong>SMTP Host: </strong><span class="editText" id="smtpHost">%s</span>
</p><br />', $mailer['smtpUser'], $mailer['smtpPass'], $mailer['smtpHost']);


echo '<button type="submit" name="mailerTest">Test SMTP</button>';
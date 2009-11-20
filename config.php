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
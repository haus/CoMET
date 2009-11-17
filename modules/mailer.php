<h1>Auto-reminder Mailer Settings</h1>
<h4>Available tags to use: <em>[firstName], [lastName], [balance], [sharePrice], [dueDate], [paymentPlan]</em></h4>
<div class="leftSidebar">
	<br />
	<h3>Coming Due Message (# of days to message: <input type="text" name="comingDueDays" size="5" maxlength="3" />)</h3>
	<textarea rows="5" cols="75"></textarea>
	
	<br /><br />
	<h3>Past Due Message (# of days to message: <input type="text" name="pastDueDays" size="5" maxlength="3" />)</h3>
	<textarea rows="5" cols="75"></textarea>

	<br /><br />
	<h3>Inactive Message (# of days to message: <input type="text" name="inactiveDays" size="5" maxlength="3" />)</h3>
	<textarea rows="5" cols="75"></textarea>

	<br /><br />	
	<h3>SMTP Settings</h3>
	<p>
		<strong>User: </strong><input type="text" name="smtpUser" /> 
		<strong>Password: </strong><input type="password" name="smtpPassword" /> 
		<strong>SMTP Host: </strong><input type="text" name="smtpHost" />
	</p>
	
	<br />
	<button type="submit" name="mailerTest">Test SMTP</button>
	<button type="submit" name="mailerSubmit">Submit</button>
</div>
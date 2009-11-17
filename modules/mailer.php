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
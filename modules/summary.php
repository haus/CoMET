<?php

if (!function_exists('checkPage'))
	require_once('../includes/functions.php');
	
checkPage('index.php');

printf('<div id="summary">
		<p>
			<strong>Card No: </strong>9999<br />
			<strong>Join Date: </strong>12/12/2008<br />
			<strong>Share Price: </strong>$180<br />
			<strong>Total Paid: </strong>$90<br />
			<strong>Remaining To Pay: </strong>$90<br />
			<strong>Next Payment Due: </strong>07/30/2010<br />
			<strong>Last Payment Made: </strong>07/30/2009
		</p>
	</div>');

?>
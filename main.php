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
require_once('./includes/config.php');
require_once('./includes/mysqli_connect.php');

$cardQ = "SELECT MAX(cardNo) FROM details WHERE cardNo < 9999";
$cardR = mysqli_query($DBS['comet'], $cardQ);
if (!$cardR) printf('Query: %s, Error: %s', $cardQ, mysqli_error($DBS['comet']));
list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
if (is_null($_SESSION['cardNo'])) $_SESSION['cardNo'] = '1';
?>

<div class="topbar" id="mainNav">
	<span style="float: left;">
		<input type="submit" id="first" value="&lt;&lt;&lt;" />
		<input type="submit" id="prev" value="&lt;" />
		<input type="submit" id="next" value="&gt;" />
		<input type="submit" id="last" value="&gt;&gt;&gt;" />
	</span>
	<strong>Current Record #<?php echo $_SESSION['cardNo']; ?></strong>
	<span style="float:right;"><input type="submit" id="new" value="New Member" /></span>
</div>
<div class="quadrant" id="onepoint1">
	<div id="owner"></div>
	<div id="details"></div>
</div>
<div class="quadrant" id="onepoint2">
	<div id="summary"></div>
</div>
<div class="quadrant" id="twopoint1">
Notes
</div>
<div class="quadrant" id="twopoint2">
	<div id="payments"></div>
</div>
<div class="quadrant" id="twopoint3">
Subscriptions
</div>
<script type="text/JavaScript">
		$('#owner').load('./modules/owner.php');
		$('#details').load('./modules/details.php');
		$('#summary').load('./modules/summary.php');
		$('#payments').load('./modules/payments.php');
</script>
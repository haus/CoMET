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
?>
<script type="text/JavaScript">
		$('#owner').load('./modules/owner.php');
		$('#details').load('./modules/details.php');
		$('#summary').load('./modules/summary.php');
</script>
<div class="topbar" id="mainNav">
	<span style="float: left;">
		<button id="first">&lt;&lt;&lt;</button>
		<button id="prev">&lt;</button>
		<button id="next">&gt;</button>
		<button id="last">&gt;&gt;&gt;</button>
	</span>
	<strong>Current Record #1234</strong>
	<span style="float:right;"><button id="new">New Member</button></span>
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
Payments
</div>
<div class="quadrant" id="twopoint3">
Subscriptions
</div>
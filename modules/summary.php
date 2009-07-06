<?php
/*
		CoMET is a stand-alone member equity tracking application designed to integrate with IS4C and Fannie.
	    Copyright (C) 2009  Matthaus Litteken

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
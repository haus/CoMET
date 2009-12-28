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

/**
 * This page will load a user management tab when it is ready.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

require_once('./includes/config.php');
?>
<form id="userForm" method="POST" name="userForm" action="./handlers/userHandler.php">

<?php
$userQ = "SELECT * FROM users";
$userR = mysqli_query($DBS['comet'], $userQ);

while (list($name, $password, $level, $userID, $email) = mysqli_fetch_row($userR)) {
	printf('<p>%s - %s - %s - %s</p>', $name, $level, $userID, $email);
}

?>
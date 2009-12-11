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

	ob_start();
	session_start();
	require_once('includes/config.php');
	require_once('includes/mysqli_connect.php');
	require_once('PEAR.php');
	require_once('MDB2.php');
	require_once('Auth/Auth.php');

//	$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet');
		
	$_SESSION['authObject'] = new Auth("MDB2", $_SESSION['authParams']);

	// Login bizness...
	//$_SESSION['authObject']->setLoginCallback('myLoginCallback');
	$_SESSION['authObject']->start();
	
	$err = $_SESSION['authObject']->addUser('matthaus2', 'lemoncoke', array('level' => 3));

	if ($err != 1) {
		// Fields not set or don't match.
		print_r($err);
		die();
	}


?>
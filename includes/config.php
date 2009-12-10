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

// CoMET DB Connection details.
$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet_structure');

$DBS['comet'] = mysqli_connect(
		$_SESSION['DB']['host'], 
		$_SESSION['DB']['user'], 
		$_SESSION['DB']['password'], 
		$_SESSION['DB']['database']
	) 
	or die('comet fail!');
	
/**
 *	Store specific values.
*/
$configQ = "SELECT name, value FROM options";
$configR = mysqli_query($DBS['comet'], $configQ);
if (!$configR) {
	printf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $configQ);
	exit();
}

while (list($name, $value) = mysqli_fetch_row($configR)) {
	if ($name == 'discounts') {
		$_SESSION[$name] = explode(',', $value);
	} else {
		$_SESSION[$name] = $value;
	}
}

/**
 *	Database Connections
*/
	
$DBS['is4c_op'] = @mysqli_connect(
		$_SESSION['opHost'], 
		$_SESSION['opUser'], 
		$_SESSION['opPass'], 
		$_SESSION['opDB']
	);

$DBS['is4c_log'] = @mysqli_connect(
		$_SESSION['logHost'], 
		$_SESSION['logUser'], 
		$_SESSION['logPass'], 
		$_SESSION['logDB']
	);

// Pear Auth Config Info
$_SESSION['authParams'] = array(
	"dsn" => "mysqli://" . $_SESSION['DB']['user'] . ":" . $_SESSION['DB']['password'] . "@" . $_SESSION['DB']['host'] . "/" . $_SESSION['DB']['database'],
	"table" => "users",
	"usernamecol" => "user",
	"passwordcol" => "password",
	"cryptType" => "md5",
	"db_fields" => array('user', 'userID', 'level', 'email')
	);

?>

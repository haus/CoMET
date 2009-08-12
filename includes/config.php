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

/**
 *	Store specific values.
*/

// Number of people in a household, on a membership.
$_SESSION['houseHoldSize'] = 2;

// Variety of discounts offered to members & workers.
$_SESSION['discounts'] = array(0,2,5,15);

// Default share price.
$_SESSION['sharePrice'] = 180.00;

/**
 *	Database Information
*/

// IS4C Connection Details (Needs select, insert, update on both DBs)
$_SESSION['is4c_op'] = array('host' => 'localhost', 'user' => 'root', 'password' => 'lemoncoke', 'database' => 'is4c_op');
$_SESSION['is4c_log'] = array('host' => 'localhost', 'user' => 'root', 'password' => 'lemoncoke', 'database' => 'is4c_op');

// CoMET DB Connection details.
$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet');

// Pear Auth Config Info
$_SESSION['authParams'] = array(
	"dsn" => "mysqli://" . $_SESSION['DB']['user'] . ":" . $_SESSION['DB']['password'] . "@" . $_SESSION['DB']['host'] . "/" . $_SESSION['DB']['database'],
	"table" => "users",
	"usernamecol" => "user",
	"passwordcol" => "password",
	"db_fields" => array('user', 'userID', 'level')
	);

?>
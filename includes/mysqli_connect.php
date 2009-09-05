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
 *	DB Connect Statements
*/
$DBS['is4c_op'] = mysqli_connect(
		$_SESSION['is4c_op']['host'], 
		$_SESSION['is4c_op']['user'], 
		$_SESSION['is4c_op']['password'], 
		$_SESSION['is4c_op']['database']
	) 
	or die('is4c_op fail! ' . "<br />Error: " . mysqli_connect_error());

$DBS['is4c_log'] = mysqli_connect(
		$_SESSION['is4c_log']['host'], 
		$_SESSION['is4c_log']['user'], 
		$_SESSION['is4c_log']['password'], 
		$_SESSION['is4c_log']['database']
	) 
	or die('is4c_log fail!');

$DBS['comet'] = mysqli_connect(
		$_SESSION['DB']['host'], 
		$_SESSION['DB']['user'], 
		$_SESSION['DB']['password'], 
		$_SESSION['DB']['database']
	) 
	or die('comet fail!');

?>
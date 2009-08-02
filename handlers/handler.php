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

//print_r($_POST);

// Process the data, update as needed.

// Read the submit type, adjust the $_SESSION['cardNo'] and let the main.php JS handle updating the divs
switch ($_POST['navButton']) {
	case 'nextRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo > {$_SESSION['cardNo']} ORDER BY cardNo ASC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		
		echo $_SESSION['cardNo'];
	break;

	case 'prevRecord':
		$cardQ = "SELECT cardNo FROM details WHERE cardNo < {$_SESSION['cardNo']} ORDER BY cardNo DESC LIMIT 1";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		if (mysqli_num_rows($cardR) == 1)
			list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
			
		echo $_SESSION['cardNo'];
	break;

	case 'firstRecord':
		$cardQ = "SELECT MIN(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo $_SESSION['cardNo'];
	break;

	case 'lastRecord':
		$cardQ = "SELECT MAX(cardNo) FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo $_SESSION['cardNo'];
	break;
	
	case 'new':
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo $_SESSION['cardNo'];
	break;
	
	default:
		$cardQ = "SELECT MAX(cardNo)+1 FROM details";
		$cardR = mysqli_query($DBS['comet'], $cardQ);
		list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
		echo $_SESSION['cardNo'];
	break;
		
}
?>
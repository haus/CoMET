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
//print_r($_POST);

// Process the data, update as needed.

// Read the submit type, adjust the $_SESSION['cardNo'] and let the main.php JS handle updating the divs
switch ($_POST['navButton']) {
	case 'nextRecord':
		echo 'next';
	break;

	case 'prevRecord':
		echo 'prev';
	break;

	case 'firstRecord':
		echo 'first';
	break;

	case 'lastRecord':
		echo 'last';
	break;
	
	case 'new':
		echo 'new';
	break;
	
	default:
		echo 'default';
	break;
		
}
?>
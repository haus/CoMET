<?php
session_start();

/**
 * This page feeds the autocomplete for the search fields in the main tab.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

require_once('../includes/config.php');
require_once('../includes/functions.php');

if (isset($_GET['q'])) {
	$searchFor = escapeData($DBS['comet'], $_GET['q']);
}

if (isset($_GET['search'])) {
	$searchBy = escapeData($DBS['comet'], $_GET['search']);
}

if (isset($searchBy)) {
	switch ($searchBy) {
		case 'first':
			$searchQ = "SELECT CONCAT(firstName, ' ', lastName, ' [', cardNo, ']') 
				FROM owners 
				WHERE firstName LIKE '$searchFor%' 
				ORDER BY firstName ASC";
			break;
			
		case 'last':
			$searchQ = "SELECT CONCAT(lastName, ', ', firstName, ' [', cardNo, ']') 
				FROM owners 
				WHERE lastName LIKE '$searchFor%' 
				ORDER BY lastName ASC";
			break;
		
		default:
			$searchQ = NULL;
			break;
	}
}

$results = '';

if (isset($searchQ)) {
	$searchR = mysqli_query($DBS['comet'], $searchQ);
	if ($searchR) {
		while (list($name) = mysqli_fetch_row($searchR)) {
			$results .= $name . "\n";
		}
		
		$results = substr($results, 0, -1);
	} else {
		printf('Query: %s, Error: %s', $searchQ, mysqli_error($DBS['comet']));
	}

}

echo $results;

?>
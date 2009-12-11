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

require_once('./includes/config.php');

define('FPDF_FONTPATH','./includes/fpdf/font/');

require_once('./includes/fpdf/fpdf.php');
require_once('./includes/fpdf/label/PDF_Label.php');

$pdf = new PDF_Label('5160');

$pdf->Open();
$pdf->AddPage();
$pdf->Set_Font_Size(10);

// Print labels...
$labelQ = "SELECT d.cardNo, count(o.cardNo), 
		CASE WHEN SUBSTR(address,-1) = '\n' THEN SUBSTR(address, 1, LENGTH(address)-1) ELSE address END AS address, 
		city, state, 
		CASE WHEN LENGTH(zip)>5 THEN CONCAT(SUBSTR(zip,1,5), '-', SUBSTR(zip,5,4)) ELSE zip END AS zip 
	FROM details AS d
	INNER JOIN owners AS o
	ON d.cardNo = o.cardNo
	WHERE address IS NOT NULL AND address <> '' AND address <> 'n/a'
		AND city IS NOT NULL AND city <> ''
		AND state IS NOT NULL AND state <> ''
		AND zip IS NOT NULL AND zip <> '' AND zip <> 0
		AND d.noMail = false
		AND o.memType IN (1, 2, 7)
	GROUP BY cardNo
	ORDER BY zip ASC";
$labelR = mysqli_query($DBS['comet'], $labelQ);

if (!$labelR) {
	printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $labelQ);
	exit();
} else {
	while (list($cardNo, $count, $address, $city, $state, $zip) = mysqli_fetch_row($labelR)) {
		
		$detailQ = "SELECT CONCAT(firstName, ' ', lastName) AS name FROM owners WHERE cardNo = $cardNo AND personNum = 1";
		$detailR = mysqli_query($DBS['comet'], $detailQ);
		
		if (!$detailR) {
			printf('Error: %s, Query: %s', mysqli_error($DBS['comet']), $detailQ);
			exit();
		}
		
		list($name) = mysqli_fetch_row($detailR);
		
		if ($count != 1) {
			for ($i = 2; $i <= $count; $i++) {
				$detailQ = "SELECT CONCAT(' & ', firstName, ' ', lastName) AS name FROM owners WHERE cardNo = $cardNo AND personNum = $i";
				$detailR = mysqli_query($DBS['comet'], $detailQ);
				list($newName) = mysqli_fetch_row($detailR);
				$name .= $newName;
			}
		}
		
		$pdf->Add_Label(sprintf("%s\n%s\n%s, %s, %s", substr($name, 0, 37), $address, $city, $state, $zip));
		
	}

	$pdf->Output();
}

?>
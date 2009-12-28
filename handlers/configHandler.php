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
 * This handler deals with updates from the config page to update the options table in the DB.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 * @todo Add system email information and an admin email in testing area for more dynamic options.
 */

require_once('../includes/config.php');
require_once('../includes/functions.php');

$planQ = "SELECT * FROM paymentPlans ORDER BY planID ASC";
$planR = mysqli_query($DBS['comet'], $planQ);
if (!$planR) printf('Query: %s, Error: %s', $planQ, mysqli_error($DBS['comet']));

while (list($planID, $freq, $amount) = mysqli_fetch_row($planR)) {
	$plan[$planID] = sprintf('%s',
		($freq > 1 ? '$' . $amount . ", $freq times per year" : '$' . $amount . " annually")
	);
}

if ($DBS['is4c_op'] !== FALSE) {
	$memTypeQ = "SELECT memType, CONCAT(SUBSTR(memdesc, 1, 1), LOWER(SUBSTR(memdesc, 2, LENGTH(memdesc)))) FROM memtype ORDER BY memType ASC";
	$memTypeR = mysqli_query($DBS['is4c_op'], $memTypeQ);

	while (list($num, $desc) = mysqli_fetch_row($memTypeR)) {
		$memType[$num] = $desc;
	}

	$staffQ = "SELECT staff_no, CONCAT(SUBSTR(staff_desc, 1, 1), LOWER(SUBSTR(staff_desc, 2, LENGTH(staff_desc)))) FROM staff ORDER BY staff_no ASC";
	$staffR = mysqli_query($DBS['is4c_op'], $staffQ);

	while (list($num, $desc) = mysqli_fetch_row($staffR)) {
		$staffList[$num] = $desc;
	}
}

$state_list = array('AL'=>"Alabama",
                'AK'=>"Alaska", 
                'AZ'=>"Arizona", 
                'AR'=>"Arkansas", 
                'CA'=>"California", 
                'CO'=>"Colorado", 
                'CT'=>"Connecticut", 
                'DE'=>"Delaware", 
                'DC'=>"District Of Columbia", 
                'FL'=>"Florida", 
                'GA'=>"Georgia", 
                'HI'=>"Hawaii", 
                'ID'=>"Idaho", 
                'IL'=>"Illinois", 
                'IN'=>"Indiana", 
                'IA'=>"Iowa", 
                'KS'=>"Kansas", 
                'KY'=>"Kentucky", 
                'LA'=>"Louisiana", 
                'ME'=>"Maine", 
                'MD'=>"Maryland", 
                'MA'=>"Massachusetts", 
                'MI'=>"Michigan", 
                'MN'=>"Minnesota", 
                'MS'=>"Mississippi", 
                'MO'=>"Missouri", 
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio", 
                'OK'=>"Oklahoma", 
                'OR'=>"Oregon", 
                'PA'=>"Pennsylvania", 
                'RI'=>"Rhode Island", 
                'SC'=>"South Carolina", 
                'SD'=>"South Dakota",
                'TN'=>"Tennessee", 
                'TX'=>"Texas", 
                'UT'=>"Utah", 
                'VT'=>"Vermont", 
                'VA'=>"Virginia", 
                'WA'=>"Washington", 
                'WV'=>"West Virginia", 
                'WI'=>"Wisconsin", 
                'WY'=>"Wyoming");

if (isset($_POST['submitted'])) {
	switch($_POST['testType']) {
		case 'smtpTest':
			require_once('Mail.php');
			
			$smtpQ = "SELECT name, value FROM options WHERE name IN ('smtpHost', 'smtpUser', 'smtpPass')";
			$smtpR = mysqli_query($DBS['comet'], $smtpQ);
			
			if (!$smtpR) {
				$output['errorMsg'] = sprintf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $smtpQ);
			} else {
				while (list($name, $value) = mysqli_fetch_row($smtpR)) {
					$smtp[$name] = $value;
				}
				
				$from = "CoMET <comet@albertagrocery.coop>";
				$to = "Matthaus <mlitteken@gmail.com>";
				$subject = "Testing...";
				$body = "Testing...";

				$host = $smtp['smtpHost'];
				$user = $smtp['smtpUser'];
				$pass = $smtp['smtpPass'];

				$headers = array ('From' => $from,
				  'To' => $to,
				  'Subject' => $subject);

				$smtp = Mail::factory(
					'smtp',
					array (
						'host' => $host,
				    	'auth' => true,
					    'username' => $user,
					    'password' => $pass
					)
				);

				$mail = $smtp->send($to, $headers, $body);

				if (PEAR::isError($mail)) {
					$output['smtpResult'] = '<blink>' . $mail->getMessage() . '</blink>';
				} else {
					$output['smtpResult'] = 'Success. Test email sent.';
				}
			}
			
			break;
			
			case 'systemTest':
				require_once('Mail.php');

				$smtpQ = "SELECT name, value FROM options WHERE name IN ('smtpHost', 'systemUser', 'systemPass')";
				$smtpR = mysqli_query($DBS['comet'], $smtpQ);

				if (!$smtpR) {
					$output['errorMsg'] = sprintf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $smtpQ);
				} else {
					while (list($name, $value) = mysqli_fetch_row($smtpR)) {
						$smtp[$name] = $value;
					}

					$from = "CoMET <comet@albertagrocery.coop>";
					$to = "Matthaus <mlitteken@gmail.com>";
					$subject = "Testing...";
					$body = "Testing...";

					$host = $smtp['smtpHost'];
					$user = $smtp['systemUser'];
					$pass = $smtp['systemPass'];

					$headers = array ('From' => $from,
					  'To' => $to,
					  'Subject' => $subject);

					$smtp = Mail::factory(
						'smtp',
						array (
							'host' => $host,
					    	'auth' => true,
						    'username' => $user,
						    'password' => $pass
						)
					);

					$mail = $smtp->send($to, $headers, $body);

					if (PEAR::isError($mail)) {
						$output['systemResult'] = '<blink>' . $mail->getMessage() . '</blink>';
					} else {
						$output['systemResult'] = 'Success. Test email sent.';
					}
				}

				break;
				
			case 'opTest':
			$opQ = "SELECT name, value FROM options WHERE name IN ('opHost', 'opUser', 'opPass', 'opDB')";
			$opR = mysqli_query($DBS['comet'], $opQ);
	
			if (!$opR) {
				$output['errorMsg'] = sprintf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $opQ);
			} else {
				while (list($name, $value) = mysqli_fetch_row($opR)) {
					$db[$name] = $value;
				}
		
				$output['opResult'] = sprintf('%s', 
					(@mysqli_connect($db['opHost'], $db['opUser'], $db['opPass'], $db['opDB']) 
						? 'Successfully connected.' : '<blink>Connection failure: ' . mysqli_connect_error()) . '</blink>');
			}
			
			break;
			
		case 'logTest':
			$logQ = "SELECT name, value FROM options WHERE name IN ('logHost', 'logUser', 'logPass', 'logDB')";
			$logR = mysqli_query($DBS['comet'], $logQ);
	
			if (!$logR) {
				$output['errorMsg'] = sprintf('MySQL Error: %s, Query: %s', mysqli_error($DBS['comet']), $logQ);
			} else {
				while (list($name, $value) = mysqli_fetch_row($logR)) {
					$db[$name] = $value;
				}
		
				$output['logResult'] = sprintf('%s', 
					(@mysqli_connect($db['logHost'], $db['logUser'], $db['logPass'], $db['logDB']) 
						? 'Successfully connected.' : '<blink>Connection failure: ' . mysqli_connect_error()) . '</blink>');
			}
			
			break;

		default:
			print_r($_POST);
			break;
	}
	echo json_encode($output);
} elseif (isset($_GET['json'])) {
	switch ($_GET['json']) {
		case 'defaultPlan':
			echo json_encode($plan);
			
			break;
		case 'defaultDiscount':
			echo json_encode($_SESSION['discounts']);
			
			break;
			
		case 'defaultStaff':
			echo json_encode($staffList);
			
			break;
		
		case 'defaultMemType':
			echo json_encode($memType);
			
			break;
		
		case 'defaultState':
			echo json_encode($state_list);
			break;
			
		default:
			
			break;
			
	}
} else {
	$allowed = array(
		'smtpHost', 
		'smtpUser', 'smtpPass',
		'systemUser', 'systemPass',
		'opHost', 'opUser', 'opPass', 'opDB', 
		'logHost', 'logUser', 'logPass', 'logDB',
		'houseHoldSize', 'discounts', 'sharePrice', 'defaultPayment', 'defaultPlan', 
		'defaultStaff', 'defaultMemType', 'defaultState', 'defaultDiscount', 'syncURL');
	
	$passArray = array('smtpPass', 'opPass', 'logPass', 'systemPass');
	$numericArray = array('houseHoldSize', 'defaultPlan', 'sharePrice', 'defaultPayment');

	if (isset($_POST['id']) && isset($_POST['value']) && in_array($_POST['id'], $allowed)) {
		$id = escapeData($DBS['comet'], $_POST['id']);
		$value = escapeData($DBS['comet'], $_POST['value']);
	} else {
		$id = NULL;
		$value = NULL;
	}

	if (!empty($id)) {
		$valueQ = "SELECT value FROM options WHERE name='$id'";
		$valueR = mysqli_query($DBS['comet'], $valueQ);
		list($oldValue) = mysqli_fetch_row($valueR);

		if (empty($value) && $value == 0 && !in_array($id, $passArray)) {
			$value = "0";
		}
			
		if (empty($value) && $value != "0") {
			// If empty or non-numeric when supposed to be then load and display the initial value...
			echo (in_array($id, $passArray) ? '(hidden)' : $oldValue);
			exit();
		} else {
			$updateQ = sprintf("UPDATE options SET value='%s' WHERE name='%s'", ($id == 'defaultDiscount' ? $_SESSION['discounts'][$value] :$value), $id);
			$updateR = mysqli_query($DBS['comet'], $updateQ);
			if ($updateR && mysqli_affected_rows($DBS['comet']) == 1) {
				echo (in_array($id, $passArray) ? '(hidden)' : 
					($id == 'defaultPlan' ? $plan[$value] : 
						($id == 'defaultDiscount' ? $_SESSION['discounts'][$value] :
							($id == 'defaultStaff' ? $staffList[$value] : 
								($id == 'defaultMemType' ? $memType[$value] :
									($id == 'defaultState' ? $state_list[$value] : $value))))));
			} else {
				echo (in_array($id, $passArray) ? '(hidden)' : 
					($id == 'defaultPlan' ? $plan[$oldValue] : 
						($id == 'defaultDiscount' ? $_SESSION['discounts'][$oldValue] : 
							($id == 'defaultStaff' ? $staffList[$oldValue] :
								($id == 'defaultMemType' ? $memType[$oldValue] : 
									($id == 'defaultState' ? $state_list[$oldValue] : $oldValue))))));
			}
		}
	}

	if (empty($value) && in_array($id, $passArray)) {
		echo '(hidden)';
	}
}
?>
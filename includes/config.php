<?php
/**
 *	Store specific values.
*/

// Number of people in a household, on a membership.
$_SESSION['houseHoldSize'] = 2;

// Variety of discounts offered to members & workers.
$_SESSION['discounts'] = array(0,2,5,15);

// Default share price.
$_SESSION['sharePrice'] = 180.00;

// Payment plan options.
$_SESSION['paymentPlans'] = array();

/**
 *	Database Information
*/

// IS4C Connection Details (Needs select, insert, update on both DBs)
$_SESSION['is4c_op'] = array('host' => 'localhost', 'user' => 'root', 'password' => 'lemoncoke', 'database' => 'is4c_op');
$_SESSION['is4c_log'] = array('host' => 'localhost', 'user' => 'root', 'password' => 'lemoncoke', 'database' => 'is4c_op');

// CoMET DB Connection details.
$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet');

?>
<?php
/**
 *	DB Connect Statements
*/
$DBS['is4c_op'] = mysqli_connect(
		$_SESSION['is4c_op']['host'], 
		$_SESSION['is4c_op']['user'], 
		$_SESSION['is4c_op']['password'], 
		$_SESSION['is4c_op']['database']
	) 
	or die('is4c_op fail!');

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
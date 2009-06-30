<?php
function checkPage($page) {
	if (substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/')+1, 30) !== $page) {
		header('location:../index.php');
		exit();
	}
}
?>
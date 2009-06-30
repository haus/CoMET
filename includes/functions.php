<?php
/**
 * Functions repository for CoMET. Holds important and generalized functions.
 *
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 0.1
 * @package CoMET
 */

/**
 * checkPage function: checks if the current page is the passed string. If not, it redirects
 * to index.php.
 * @param $page a string page name to check the current page against
 * @return none
 */
function checkPage($page) {
	if (substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/')+1, 30) !== $page) {
		header('location:../index.php');
		exit();
	}
}

/**
 * escape_data function: sanitizes trimmed input using mysqli_real_escape_string, if it exists.
 * @param &$connection reference to the mysqli connection to use in the escaping
 * @param $data string/data to be escaped for safe insertion into MySQL DB.
 * @return sanitized data ready for insertion into MySQL DB.
 */
if (!function_exists('escape_data')) {	
	function escape_data(&$connection, $data) {
		if (function_exists('mysqli_real_escape_string'))
			return mysqli_real_escape_string($connection, trim($data));
		else
			return trim($data);
	}
}
?>
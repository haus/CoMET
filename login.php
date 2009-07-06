<?php
	ob_start();
	require_once('includes/config.php');
	require_once('includes/mysqli_connect.php');
	require_once('PEAR.php');
	require_once('MDB2.php');
	require_once('Auth/Auth.php');
	
	$params = array(
		"dsn" => "mysqli://root:lemoncoke@localhost/CoMET",
		"table" => "users",
		"usernamecol" => "user",
		"passwordcol" => "password",
		"db_fields" => array('user', 'userID', 'level')
		);
		
	$a = new Auth("MDB2", $params, 'myLogin');
	
	// Login bizness...
	$a->setLoginCallback('myLoginCallback');
	$a->start();
	
	// Logout...
	if ($a->getAuth() && isset($_GET['act']) && $_GET['act'] == "logout") {
		$a->setLogoutCallback('myLogoutCallback');
		session_unset();
		$a->logout();
		$a->start();
		ob_end_clean();
		header("Location:index.php");
	}
	
	if (isset($_POST['register']) || isset($_POST['login'])) {
	
		if ($a->getAuth()) {
			$_SESSION['user'] = $a->getUsername();
			$_SESSION['level'] = $a->getAuthData('level');
			
			ob_end_clean();
			printf('<script type="text/JavaScript">
				$(document).ready(function() {
					$("#tabs").tabs("select", 0);
				});</script>');
			header("Location:index.php");
		
			if ($_SESSION['level'] == 3) { // If admin
				// Add user stuff...Only for admins (userLevel = 3). Needs to go inside the auth if.
				if (isset($_POST['register'])) {
					if ($_POST['username'] && ($_POST['password'] == $_POST['confirm'])) {
						$err = $a->addUser($_POST['username'], $_POST['password'], array('level' => $_POST['level']));

						if ($err == 1) {
							// This doesn't belong I don't think if someone else is adding users.
							$a->start();
						} else {
							// Fields not set or don't match.
							print_r($err);
							die();
						}
					} else {
						// Display registration form.
						myRegister();
					}
				} // End of $_POST['register']
			} // End of $user['level'] == 3
		} else {
			// Failed to login...
			echo '<font color="#990000">Invalid username or password.</font>';
		} // End of $a->getAuth(); if
	} // End of $_POST['register'] || $_POST['login']
	
	// Logged in?
	if (isset($_SESSION['user'])) {
		printf('%s is logged in.', $_SESSION['user']);
	}
	
	function myLogin() {
		printf('
			<p class="login">
				<form name="login" method="post" action="%s">
					User Name: <input type="text" name="username" value="%s" />
					Password: <input type="password" name="password" size="10" />
					<input type="submit" name="login" id="login" value="Login" />
				</form>
			</p>', $_SERVER['PHP_SELF'], (isset($_POST['username']) ? $_POST['username'] : NULL));
	} // End of myLogin()
	
	function myRegister() {
		printf('
			<p class="login">
				<form name="register" method="post" action="%s">
					User Name: <input type="text" name="username" value="%s" />
					Password: <input type="password" name="password" size="10" />
					Confirm Password: <input type="password" name="confirm" size="10" />
					User Level: <select name="level">
						<option value="0">System</option>
						<option value="1">User</option>
						<option value="2">Editor</option>
						<option value="3">Administrator</option>
					</select>
					<input type="submit" id="register" name="register" value="Add User" />
				</form>
			</p>', $_SERVER['PHP_SELF'], (isset($_POST['username']) ? $_POST['username'] : NULL));
	} // End of myRegister()
	
	function myLoginCallback() {
		
	}
	
	function myLogoutCallback() {
		
	}
?>
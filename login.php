<?php
	ob_start();
	require_once('includes/config.php');
	require_once('includes/mysqli_connect.php');
	require_once('PEAR.php');
	require_once('MDB2.php');
	require_once('Auth/Auth.php');

//	$_SESSION['DB'] = array('host'=>'localhost', 'user'=>'root', 'password'=>'lemoncoke', 'database'=>'comet');
	$params = array(
		"dsn" => "mysqli://" . $_SESSION['DB']['user'] . ":" . $_SESSION['DB']['password'] . "@" . $_SESSION['DB']['host'] . "/" . $_SESSION['DB']['database'],
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
		?>
		<script type="text/javascript">
			$('tabs').tabs('select', 0);
		</script>
		<?php
	}
	
	// Add user. Admin only.
	if ($a->getAuth() && isset($_GET['act']) && $_SESSION['level'] == 3) {
		// Add user stuff...Only for admins (userLevel = 3). Needs to go inside the auth if.
		if (isset($_POST['register'])) {
			if ($_POST['username'] && ($_POST['password'] == $_POST['confirm'])) {
				$err = $a->addUser($_POST['username'], $_POST['password'], array('level' => $_POST['level']));

				if ($err != 1) {
					// Fields not set or don't match.
					print_r($err);
					die();
				}
			} else {
				// Display registration form.
				myRegister();
			}
		}
	}
	
	if (isset($_POST['submitted'])) {
	
		if ($a->getAuth()) {
			$_SESSION['user'] = $a->getUsername();
			$_SESSION['level'] = $a->getAuthData('level');
			ob_end_clean();
		} else {
			// Failed to login...
			echo '<font color="#990000">Invalid username or password.</font>';
		} // End of $a->getAuth(); if
	} // End of $_POST['login']
	
	// Logged in?
	if (isset($_POST['submitted']) && $a->checkAuth() && isset($_SESSION['user'])) {
		echo 'TRUE';
	} elseif (isset($_POST['submitted'])) {
		echo 'FALSE';
	} elseif ($a->checkAuth() && isset($_SESSION['user'])) {
		echo '<script type="text/javascript">
				self.parent.tb_remove();
				self.parent.getElementById();
			</script>';
	}
	
	function myLogin() {
		// The javascript to submit.
		//	<button type="submit" onclick="window.top.tb_remove();">Close</button>
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
			<title>CoMET - Co-operative Member Equity Tracking</title>
		<script src="./includes/javascript/jquery-1.3.2.min.js" type="text/javascript"></script>
		<script src="./includes/javascript/jquery.form.js" type="text/javascript"></script>
		<script type="text/JavaScript">
			// pre-submit callback 
			function showRequest(formData, jqForm, options) { 
			    // formData is an array; here we use $.param to convert it to a string to display it 
			    // but the form plugin does this for you automatically when it submits the data 
			    var queryString = $.param(formData); 

			    // jqForm is a jQuery object encapsulating the form element.  To access the 
			    // DOM element for the form do this: 
			    // var formElement = jqForm[0]; 

			    alert('About to submit: \n\n' + queryString); 

			    // here we could return false to prevent the form from being submitted; 
			    // returning anything other than false will allow the form submit to continue 
			    return false; 
			}
		
			// pre-submit callback 
			function validate(formData, jqForm, options) {
			    // formData is an array of objects representing the name and value of each field 
			    // that will be sent to the server;  it takes the following form: 
			    // 
			    // [ 
			    //     { name:  username, value: valueOfUsernameInput }, 
			    //     { name:  password, value: valueOfPasswordInput } 
			    // ] 
			    // 
			    // To validate, we can examine the contents of this array to see if the 
			    // username and password fields have values.  If either value evaluates 
			    // to false then we return false from this method. 
			    for (var i=0; i < formData.length; i++) {
			        if (!formData[i].value) {
			            alert('Please enter a value for both Username and Password'); 
			            return false; 
			        }
			    }
			}

			// post-submit callback 
			function showResponse(responseText, statusText)  { 
			    // for normal html responses, the first argument to the success callback 
			    // is the XMLHttpRequest object's responseText property 

			    // if the ajaxSubmit method was passed an Options Object with the dataType 
			    // property set to 'xml' then the first argument to the success callback 
			    // is the XMLHttpRequest object's responseXML property 

			    // if the ajaxSubmit method was passed an Options Object with the dataType 
			    // property set to 'json' then the first argument to the success callback 
			    // is the json data object returned by the server 
				if (responseText == 'TRUE') {
					self.parent.tb_remove();
				} else {
					alert('Invalid user name or password. Please try again.');
				}
			}
		</script>
		<script>
			
			//prepare the form when the DOM is ready 
			$(document).ready(function() { 
			    var options = { 
			        //target:        '#output1',   // target element(s) to be updated with server response 
			        beforeSubmit:  validate,  // pre-submit callback 
			        success:       showResponse,  // post-submit callback 

			        // other available options: 
			        url:       'login.php'         // override for form's 'action' attribute 
			        //type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
			        //dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
			        //clearForm: true        // clear all form fields after successful submit 
			        //resetForm: true        // reset the form after successful submit 

			        // $.ajax options can be used here too, for example: 
			        //timeout:   3000 
			    };
				// bind to the form's submit event 
			    $('#loginForm').submit(function() {
					$(this).ajaxSubmit(options);
					return false;
				});
			});
		</script>
		</head>
		<body>
		<?php
		// The HTML Form
		printf('
			<p>
				<form name="loginForm" id="loginForm" method="post" action="%s">
					<h2 style="text-align:center;">Log In Here</h2>
					<p>User Name: <input type="text" id="username" name="username" value="%s" /></p>
					<p>Password: <input type="password" id="password" name="password" size="10" /></p>
					<input type="hidden" name="submitted" value="TRUE" />
					<div style="text-align:center;"><input type="submit" name="loginButton" id="loginButton" value="Login" /></div>
				</form>
			</p></body></html>', $_SERVER['PHP_SELF'], (isset($_POST['username']) ? $_POST['username'] : NULL));
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
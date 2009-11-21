<?php
require_once('Mail.php');

$from = "Matthaus <matthaus@albertagrocery.coop>";
$to = "Matthaus <mlitteken@gmail.com>";
$subject = "Testing...";
$body = "Testing...";

$host = "smtp.albertagrocery.coop";
$user = "matthaus@albertagrocery.coop";
$pass = "lung*vIa";

$headers = array ('From' => $from,
  'To' => $to,
  'Subject' => $subject);

$smtp = Mail::factory('smtp',
  array ('host' => $host,
    'auth' => true,
    'username' => $user,
    'password' => $pass));

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {
  echo("<p>" . $mail->getMessage() . "</p>");
 } else {
  echo("<p>Message successfully sent!</p>");
 }
?>
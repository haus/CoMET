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
 * This cron script sends out 50 emails from the mail queue at a time.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

$baseDir = (substr(__DIR__, -1) == '/' ? substr(__DIR__, 0, -1) : __DIR__);
$baseDir = substr(__DIR__, 0, strrpos($baseDir, '/'));

require_once($baseDir . '/includes/config.php');
require_once($baseDir . '/includes/functions.php');
require_once('Mail.php');
require_once('Mail/Queue.php');

/* How many mails could we send each time the script is called */
$max_amount_mails = 50;

/* we use the db_options and mail_options from the config again  */
$mail_queue =& new Mail_Queue($_SESSION["queue_db"], $_SESSION["queue_options"]);

/* really sending the messages */
$mail_queue->sendMailsInQueue($max_amount_mails);

?>
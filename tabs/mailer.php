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
 * This page loads the mailer module into the content tab.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

require_once('../includes/config.php');
require_once('../includes/functions.php');

?>
<script type="text/javascript">
$(document).ready(function() {
	$('#mailSettings').load('./modules/mailerModule.php');
});
</script>
<div id="mailSettings" class="left">
</div>
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
require_once('./includes/config.php');

?>
<script type="text/javascript">
	function validateConfig(formData, jqForm, options) {
		// Disable the buttons when the form is submitted.
		$('#configForm :button').attr("disabled","disabled");
		return true;
	}
	
	// post-submit callback 
	function configResponse(responseText, statusText)  { 
	    // for normal html responses, the first argument to the success callback 
	    // is the XMLHttpRequest object's responseText property

	    // if the ajaxSubmit method was passed an Options Object with the dataType 
	    // property set to 'xml' then the first argument to the success callback 
	    // is the XMLHttpRequest object's responseXML property

	    // if the ajaxSubmit method was passed an Options Object with the dataType 
	    // property set to 'json' then the first argument to the success callback 
	    // is the json data object returned by the server
	
		//alert(responseText);
		//alert(responseText.message);
		if (responseText.opResult != undefined) {
			$('#opResponse').html('<strong>' + responseText.opResult + '</strong>');
		} else if (responseText.logResult != undefined) {
			$('#logResponse').html('<strong>' + responseText.logResult + '</strong>');
		} else if (responseText.errorMsg != undefined) {
			alert(responseText.errorMsg);
		} else {

		}

	 	// Enable the buttons after the frames are loaded.
		$('#configForm :button').removeAttr("disabled");

	}

	$(document).ready(function() {
		$('#configSettings').load('./modules/config.php');
	});
</script>
<div id="configSettings" class="center">
</div>
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
 * This page loads the owner, details, summary, notes, and payments modules into their respective divs.
 * @author Matthaus Litteken <matthaus@cecs.pdx.edu>
 * @version 1.0
 * @package CoMET
 */

require_once('../includes/config.php');

if (!isset($_SESSION['cardNo'])) {
	$cardQ = "SELECT MAX(cardNo) FROM details WHERE cardNo < 9999";
	$cardR = mysqli_query($DBS['comet'], $cardQ);
	if (!$cardR) printf('Query: %s, Error: %s', $cardQ, mysqli_error($DBS['comet']));
	list($_SESSION['cardNo']) = mysqli_fetch_row($cardR);
	if (is_null($_SESSION['cardNo'])) $_SESSION['cardNo'] = '1';
}

?>

<script type="text/JavaScript">
	function validate(formData, jqForm, options) {
		changed = $('#changed').val();
		
		if ($('#navButton').val() == 'delete') {
			var answer = confirm("Delete current member?");
		    if (answer) {
				return true;
		    }
		    return false;
		}
		
		// Disable the buttons when the form is submitted.
		$('#navForm :button').attr("disabled","disabled");
		$('#navForm :input').attr("disabled","disabled");
		return true;
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
	
		//alert(responseText);
		//alert(responseText.message);
		if (responseText.message == 'error') {
			$('#messageSpace').html("There was an error updating that record, please check the entry and try again.");	
		} else if (responseText.errorMsg != undefined) {
			alert(responseText.errorMsg);
		} else {
			$('#owner').load('./modules/ownersModule.php');
			$('#details').load('./modules/detailsModule.php');
			$('#summary').load('./modules/summaryModule.php');
			$('#payments').load('./modules/paymentsModule.php');
			$('#notes').load('./modules/notesModule.php');
			$('#firstSearch').val('');
			$('#lastSearch').val('');
			$('#cardNo').html(responseText.cardNo);
			$('#changed').val('false');
		}

	 	// Enable the buttons after the frames are loaded.
		$('#navForm :button').removeAttr("disabled");
		$('#navForm :input').removeAttr("disabled");
		$('#messageSpace').html(responseText.message);
	}
	
	function searchSubmit() {
		$('#navForm').submit();
	}
	
	function focusFirst() {
		$('#firstSearch').focus();
	}
	
	function focusLast() {
		$('#lastSearch').focus();
	}
	
	function triggerChange() {
		$('#changed').val('true');
	}
	
	function reload() {
		$('#summary').load('./modules/summaryModule.php');
	}
	
	$('#navForm').ready(function() {
		var options = { 
	        //target:        '#output1',   // target element(s) to be updated with server response 
	        beforeSubmit:  validate, // pre-submit callback 
	        success:       showResponse,  // post-submit callback 

	        // other available options: 
	        //url:       './modules/handler.php'         // override for form's 'action' attribute 
	        //type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
	        dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
	        //clearForm: true        // clear all form fields after successful submit 
	        //resetForm: true        // reset the form after successful submit 

	        // $.ajax options can be used here too, for example: 
	        //timeout:   3000 
	    };
	
		$('#firstSearch').autocomplete('./handlers/searchHandler.php', { mustMatch: 1, onItemSelect: focusFirst, extraParams: {search: 'first'}});
		
		$('#firstSearch').focus(function() {
			$('#lastSearch').val('');
		});
		
		$('#lastSearch').autocomplete('./handlers/searchHandler.php', { mustMatch: 1, onItemSelect: focusLast, extraParams: {search: 'last'}});
		
		$('#lastSearch').focus(function() {
			$('#firstSearch').val('');
		});
		
		// bind to the form's submit event 
	    $('#navForm').submit(function() {
			$(this).ajaxSubmit(options);
			return false;
		});
		
		$('#cardNo').editable('./handlers/mainHandler.php?navButton=customRecord', {
			width: 40,
			style: 'display: inline',
			onblur: 'submit',
			select: 'true',
			tooltip: 'Click to browse by number...',
			callback: function(value, settings) {
				$('#owner').load('./modules/ownersModule.php');
				$('#details').load('./modules/detailsModule.php');
				$('#summary').load('./modules/summaryModule.php');
				$('#payments').load('./modules/paymentsModule.php');
				$('#notes').load('./modules/notesModule.php');
				$('#firstSearch').val('');
				$('#lastSearch').val('');
				$('#cardNo').html(value);
				$('#changed').val('false');
			}
		});
		
		$('#navForm :button').click(function() {
			$('#navButton').val(this.id);
		});

		$('#owner').load('./modules/ownersModule.php');
		$('#details').load('./modules/detailsModule.php');
		$('#summary').load('./modules/summaryModule.php');
		$('#payments').load('./modules/paymentsModule.php');
		$('#notes').load('./modules/notesModule.php');
		
	});
	
	window.onbeforeunload = function() {
		if ($('#changed').val() == 'true') {
			$('#navButton').val('current');
			$('#navForm').submit();
			return "There were unsaved changes. They have now been saved.";
		}
	}
	
	function validatePayment(formData, jqForm, options) {
		if ($('#pmtDatepicker').val().length > 0 && $('#pmtAmount').val().length > 0) {
			
		} else if ($('#removeID').val() != 'false') {
			var answer = confirm("Delete selected payment?");
		    if (answer) {
				return true;
		    }
		    return false;
		} else {
			alert('You need to fill in the date and amount before submitting.');
			return false;
		}
	}
	
	function paymentResponse(responseText, statusText) {
		//alert(responseText);
		if (responseText.errorMsg != undefined) {
			alert(responseText.errorMsg);
		}
		
		if (responseText.success != undefined) {
			$('#owner').load('./modules/ownersModule.php');
			$('#summary').load('./modules/summaryModule.php');
			$('#payments').load('./modules/paymentsModule.php');
		}
	}
	
	$('#paymentForm').ready(function() {
		var options = { 
	        //target:        '#output1',   // target element(s) to be updated with server response 
	        beforeSubmit:  validatePayment, // pre-submit callback 
	        success:       paymentResponse,  // post-submit callback 

	        // other available options: 
	        //url:       './modules/handler.php'         // override for form's 'action' attribute 
	        //type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
	        dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
	        //clearForm: true        // clear all form fields after successful submit 
	        //resetForm: true        // reset the form after successful submit 

	        // $.ajax options can be used here too, for example: 
	        //timeout:   3000 
	    };
	
		// bind to the form's submit event 
	    $('#paymentForm').submit(function() {
			if ($('#changed').val() == 'true') {
				$('#navButton').val('current');
				$('#navForm').submit();
			}
			
			$(this).ajaxSubmit(options);
			return false;
		});
	});
	
	function validateNote(formData, jqForm, options) {
		if ($('#removeID').val() != 'false') {
			var answer = confirm("Delete selected note?")
		    if (answer) {
				return true;
		    }
		    return false;
		}
	}
	
	function noteResponse(responseText, statusText) {
		if (responseText.errorMsg != undefined) {
			alert(responseText.errorMsg);
		}

		if (responseText.success != undefined) {
			$('#notes').load('./modules/notesModule.php');
		}
	}
	
	$('#notesForm').ready(function() {
		var options = { 
	        //target:        '#output1',   // target element(s) to be updated with server response 
	        beforeSubmit:  validateNote, // pre-submit callback 
	        success:       noteResponse,  // post-submit callback 

	        // other available options: 
	        //url:       './modules/handler.php'         // override for form's 'action' attribute 
	        //type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
	        dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
	        //clearForm: true        // clear all form fields after successful submit 
	        //resetForm: true        // reset the form after successful submit 

	        // $.ajax options can be used here too, for example: 
	        //timeout:   3000 
	    };
	
		// bind to the form's submit event 
	    $('#notesForm').submit(function() {
			if ($('#changed').val() == 'true') {
				$('#navButton').val('current');
				$('#navForm').submit();
			}
			
			$(this).ajaxSubmit(options);
			return false;
		});
	});
</script>
<form id="navForm" method="POST" name="navForm" action="./handlers/mainHandler.php">
	<div class="topbar" id="mainNav">
		<span style="float:left;">
			<strong>Current Record #<span id="cardNo"><?php echo $_SESSION['cardNo']; ?></span></strong>
		</span>
		<span style="float:right;">
			<strong>Search By First Name: </strong><input type="text" class="search" name="firstSearch" id="firstSearch" />
			&nbsp;
			<strong>Search By Last Name: </strong><input type="text" class="search" name="lastSearch" id="lastSearch" />
		</span>
		<br style="clear:left;" />
		<span style="float:left;padding:5px;clear:right;">
			<button type="submit" name="firstRecord" value="first" id="firstRecord">&lt;&lt;&lt;</button>
			<button type="submit" name="prevRecord" value="prev" id="prevRecord">&lt;</button>
			<button type="submit" name="nextRecord" value="next" id="nextRecord">&gt;</button>
			<button type="submit" name="lastRecord" value="last" id="lastRecord">&gt;&gt;&gt;</button>
		</span>
		<span style="float:right;padding:5px;">
			<button type="submit" name="delete" id="delete" value="delete">Delete Member</button>
			<button type="submit" name="new" id="new" value="new">New Member</button>
		</span>
		<input type="hidden" name="changed" value="false" id="changed" />
		<input type="hidden" name="navButton" value="" id="navButton">
	</div>
	<div class="quadrant" id="onepoint1">
		<div id="owner"></div>
		<div id="details"></div>
	</div>
	<div class="quadrant" id="onepoint2">
		<div id="summary"></div>
	</div>
</form>
<div class="quadrant" id="twopoint1">
	<form id="notesForm" method="POST" name="notesForm" action="./handlers/notesHandler.php">
		<div id="notes"></div>
	</form>
</div>
<div class="quadrant" id="twopoint2">
	<form id="paymentForm" method="POST" name="paymentForm" action="./handlers/paymentHandler.php">
		<div id="payments"></div>
	</form>	
</div>
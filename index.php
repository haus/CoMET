<?php
require_once('./includes/header.html');
?>
<noscript>
	<p>You must have javascript enabled to use this program.</p>
</noscript>
<div class="quadrant" id="onepoint1">
<?php require_once('./modules/owner.php'); ?>
<?php require_once('./modules/details.php'); ?>
</div>
<div class="quadrant" id="onepoint2">
<?php require_once('./modules/summary.php'); ?>
</div>
<div class="quadrant" id="twopoint1">
2.1
</div>
<div class="quadrant" id="twopoint2">
2.2
</div>
<div class="quadrant" id="twopoint3">
2.3
</div>
<?php
require_once('./includes/footer.html');
?>
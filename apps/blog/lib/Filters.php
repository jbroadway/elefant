<?php

function blog_filter_title ($title) {
	return trim (preg_replace ('/[^a-z0-9-]+/', '-', strtolower ($title)), ' -');
}

function blog_filter_date ($ts, $format = 'F j, Y - g:ia') {
	$t = strtotime ($ts);
	return gmdate ($format, $t);
}

?>
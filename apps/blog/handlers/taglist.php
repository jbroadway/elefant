<?php

$page->layout = false;

header ('Content-Type: application/json');

echo json_encode (
	db_shift_array (
		'select distinct tag_id from blog_post_tag'
	)
);

?>
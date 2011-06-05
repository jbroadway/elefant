<?php

if (! $this->internal) {
	die ('Cannot add document from a browser.');
}

$failed = Form::verify_values ($this->data, 'apps/search/forms/add.php');
if (count ($failed) > 0) {
	die ('Validation error on fields: ' . join (', ', $failed));
}

require_once ('apps/search/lib/indextank_client.php');

$client = new ApiClient ($appconf['IndexTank']['private_api_url']);
$index = $client->get_index ($appconf['IndexTank']['index_name']);

$body = trim (strip_tags ($this->data['body']));
$description = array_shift (explode ('.', $body));
if (strlen ($description) > 160) {
	$description = substr ($description, 0, 157) . '...';
}
$body = $this->data['page'] . ' ' . $this->data['title'] . $body;
$url = '/' . $this->data['page'];

// hopefully 200!
return $index->add_document ($this->data['page'], array (
	'title' => $this->data['title'],
	'description' => $description,
	'text' => $body,
	'url' => $url
));

?>
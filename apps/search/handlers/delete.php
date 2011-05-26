<?php

if (! $this->internal) {
	die ('Cannot add document from a browser.');
}

if (! isset ($this->data['page']) || empty ($this->data['page'])) {
	die ('Missing required field: page');
}

require_once ('apps/search/lib/indextank_client.php');

$appconf = parse_ini_file ('apps/search/conf/config.php', true);

$client = new ApiClient ($appconf['IndexTank']['private_api_url']);
$index = $client->get_index ($appconf['IndexTank']['index_name']);

// hopefully 200!
return $index->delete_document ($this->data['page']);

?>
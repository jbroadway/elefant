<?php

$id = (isset ($data['id'])) ? $data['id'] : (isset ($_GET['id'])) ? $_GET['id'] : false;
if (! $id) {
	// no id
	return;
}

$b = new Block ($id);
if ($b->error) {
	// not found
	return;
}

// permissions
if ($b->access == 'member' && ! User::require_login ()) {
	return;
} elseif ($wp->access == 'private' && ! User::require_admin ()) {
	return;
}

if ($b->show_title == 'yes') {
	printf ('<h3>%s</h3>', $b->title);
}

echo $tpl->run_includes ($b->body);

?>
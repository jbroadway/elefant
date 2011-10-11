<?php

/**
 * Renders the specified content block. Use in layout templates like this:
 *
 *     {! blocks/my-block-id !}
 */

global $user;

$id = (isset ($this->params[0])) ? $this->params[0] : false;
if (! $id) {
	if (User::is_valid () && $user->type == 'admin') {
		echo $tpl->render ('blocks/editable', (object) array ('id' => $id, 'locked' => false));
	}
	return;
}

$lock = new Lock ('Block', $id);

$b = new Block ($id);
if ($b->error) {
	if (User::is_valid () && $user->type == 'admin') {
		echo $tpl->render ('blocks/editable', (object) array ('id' => $id, 'locked' => false));
	}
	return;
}

// permissions
if ($b->access == 'member' && ! User::require_login ()) {
	return;
} elseif ($b->access == 'private' && ! User::require_admin ()) {
	return;
}

if ($b->show_title == 'yes') {
	printf ('<h3>%s</h3>', $b->title);
}

$b->locked = $lock->exists ();

if (User::is_valid () && $user->type == 'admin') {
	echo $tpl->render ('blocks/editable', $b);
}

echo $tpl->run_includes ($b->body);

?>
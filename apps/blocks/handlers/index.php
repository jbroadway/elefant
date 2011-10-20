<?php

/**
 * Renders the specified content block. Use in layout templates like this:
 *
 *     {! blocks/my-block-id !}
 *
 * You can also specify a dynamic ID value for your blocks so that a single
 * block position can refer to a dynamic number of blocks, like this:
 *
 *     {! blocks/index?id=[id] !}
 *
 * In the above example, `[id]` is replaced with the current page ID, so
 * that on each page, it will try to render a block in that position with
 * the same ID as the current page.
 *
 * See the API documentation for the Template class for more info on
 * `[expr]` style sub-expressions.
 */

global $user;

$id = (isset ($this->params[0])) ? $this->params[0] : (isset ($data['id'])) ? $data['id'] : false;
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
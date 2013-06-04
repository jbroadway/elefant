<?php

/**
 * Renders the specified list of content blocks one after another, while
 * also fetching them in a single database query, instead of incurring
 * multiple calls to the database for multiple blocks. Use this in layout
 * templates like this:
 *
 *     {! blocks/group/block-one/block-two/block-three !}
 *
 * Or on several lines like this:
 *
 *     {! blocks/group
 *        ?id[]=block-one
 *        &id[]=block-two
 *        &id[]=block-three !}
 */

$ids = (count ($this->params) > 0) ? $this->params : (isset ($data['id']) ? $data['id'] : array ());
if (! is_array ($ids)) {
	$ids = array ($ids);
}

$qs = array ();
foreach ($ids as $id) {
	$qs[] = '?';
}

$lock = new Lock ();
$locks = $lock->exists ('Block', $ids);
$query = Block::query ()->where ('id in(' . join (', ', $qs) . ')');
$query->query_params = $ids;
$blocks = $query->fetch ();

$list = array ();
foreach ($blocks as $block) {
	$list[$block->id] = $block;
}

foreach ($ids as $id) {
	if (! isset ($list[$id])) {
		if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
			echo $tpl->render ('blocks/editable', (object) array ('id' => $id, 'locked' => false));
		}
		continue;
	}

	$b = $list[$id];

	// permissions
	if ($b->access !== 'public') {
		if (! User::require_login ()) {
			continue;
		}
		if (! User::access ($b->access)) {
			continue;
		}
	}

	if ($b->show_title == 'yes') {
		printf ('<h3>%s</h3>', $b->title);
	}

	$b->locked = in_array ($id, $locks);

	if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
		echo $tpl->render ('blocks/editable', $b);
	}

	echo $tpl->run_includes ($b->body);
}

?>
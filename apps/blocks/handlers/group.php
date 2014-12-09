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
 *
 * You can also set a `level` parameter to specify which heading level
 * to use for the block titles:
 *
 *     {! blocks/group
 *        ?id[]=block-one
 *        &id[]=block-two
 *        &level=h2 !}
 *
 * Additionally, you can set a `divs` parameter to specify that each block
 * should be wrapped in a `<div class="block" id="block-ID"></div>` tag,
 * where the `id` attribute's value is the block ID prefixed with `block-`:
 *
 *     {! blocks/group
 *        ?id[]=sidebar-promo
 *        &id[]=sidebar-support
 *        &divs=on !}
 *
 * This will output:
 *
 *     <div class="block" id="block-sidebar-promo">
 *         <h2>Block title</h2>
 *         <p>Block contents...</p>
 *     </div>
 *     <div class="block" id="block-sidebar-support">
 *         <h2>Block title</h2>
 *         <p>Block contents...</p>
 *     </div>
 *
 * In this way, blocks can easily be styled collectively or individually,
 * making them a powerful way to build site content.
 *
 * You can also specify a `units` parameter, which specifies the width of
 * each `div` as an `e-col-%d` class, for example:
 *
 *     {! blocks/group
 *        ?id[]=sidebar-promo
 *        &id[]=sidebar-support
 *        &units=75,25 !}
 *
 * This will output:
 *
 *     <div class="block e-col-75" id="block-sidebar-promo">
 *         <h2>Block title</h2>
 *         <p>Block contents...</p>
 *     </div>
 *     <div class="block e-col-25" id="block-sidebar-support">
 *         <h2>Block title</h2>
 *         <p>Block contents...</p>
 *     </div>
 *
 * The use of units will automatically include the `admin/util/minimal-grid`
 * helper, which provides a very minimal responsive grid system for
 * page content.
 */

$ids = (count ($this->params) > 0) ? $this->params : (isset ($data['id']) ? $data['id'] : array ());
if (! is_array ($ids)) {
	$ids = array ($ids);
}

if (count ($ids) === 0) {
	return;
}

$level = (isset ($data['level']) && preg_match ('/^h[1-6]$/', $data['level'])) ? $data['level'] : 'h3';
$divs = isset ($data['divs']) ? true : false;
if (isset ($data['units'])) {
	echo $this->run ('admin/util/minimal-grid');
	$units = explode (',', $data['units']);
	$divs = true;
} else {
	$units = 'auto';
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

$total = count ($blocks);
if ($units === 'auto' || $total !== count ($units)) {
	if ($total === 2) {
		$units = array (66, 33);
	} elseif ($total > 0) {
		$units = array ();
		$w = floor (100 / $total);
		for ($k = 0; $k < $total; $k++) {
			$units[$k] = $w;
		}
	}
}

foreach ($ids as $k => $id) {
	if (! isset ($list[$id])) {
		if ($divs) {
			printf ('<div class="e-col-%d block block-missing" id="block-%s">' . PHP_EOL, $units[$k], $id);
		}

		if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
			echo $tpl->render ('blocks/editable', (object) array ('id' => $id, 'locked' => false)) . PHP_EOL;
		}

		if ($divs) {
			echo '</div>' . PHP_EOL;
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

	if ($divs) {
		printf ('<div class="e-col-%d block" id="block-%s">' . PHP_EOL, $units[$k], $b->id);
	}

	if ($b->show_title == 'yes') {
		printf ('<' . $level . '>%s</' . $level . '>' . PHP_EOL, $b->title);
	}

	$b->locked = in_array ($id, $locks);

	if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
		echo $tpl->render ('blocks/editable', $b) . PHP_EOL;
	}

	echo $tpl->run_includes ($b->body) . PHP_EOL;
	
	if ($divs) {
		echo '</div>' . PHP_EOL;
	}
}

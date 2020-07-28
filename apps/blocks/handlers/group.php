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
 * Or you can specify a wildcard and it will fetch all blocks that match,
 * sorted by ID ascending:
 *
 *     {! blocks/group?wildcard=page-* !}
 *
 * This can be combined with the page ID and the rows parameter to create a
 * series of editable divs on a page, like this:
 *
 *     {! blocks/group?wildcard=[id]-*&rows=on !}
 *
 * The above tag would fetch any blocks beginning with the current page ID
 * followed by a hyphen `-` and anything else (numbers, text), and wrap them
 * in divs like this:
 *
 *     <div class="block-outer" id="block-outer-pageid-1">
 *         <div class="e-row">
 *             <div class="block e-col-100" id="block-pageid-1">
 *                 <h2>Block title</h2>
 *                 <p>Block contents...</p>
 *             </div>
 *         </div>
 *     </div>
 *     <div class="block-outer" id="block-outer-pageid-2">
 *         <div class="e-row">
 *             <div class="block e-col-100" id="block-pageid-2">
 *             <h2>Block title</h2>
 *             <p>Block contents...</p>
 *             </div>
 *         </div>
 *     </div>
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

$wildcard = isset ($data['wildcard']);

if (! $wildcard) {
	$ids = (count ($this->params) > 0) ? $this->params : (isset ($data['id']) ? $data['id'] : array ());
	if (! is_array ($ids)) {
		$ids = array ($ids);
	}

	if (count ($ids) === 0) {
		return;
	}
} else {
	$ids = [$data['wildcard']];
}

$level = (isset ($data['level']) && preg_match ('/^h[1-6]$/', $data['level'])) ? $data['level'] : 'h3';
$rows = (isset ($data['rows']) && $data['rows'] == 'on') ? true : false;
$divs = ($rows || (isset ($data['divs']) && $data['divs'] == 'on')) ? true : false;

if ($rows || $divs || isset ($data['units'])) {
	echo $this->run ('admin/util/minimal-grid');
}

if (isset ($data['units'])) {
	$units = explode (',', $data['units']);
	$divs = true;
} elseif ($rows) {
	$units = ['100'];
} else {
	$units = 'auto';
}

$lock = new Lock ();
$locks = [];
$blocks = [];
$order_id = '';

if (! $wildcard) {
	$qs = array ();
	foreach ($ids as $id) {
		$qs[] = '?';
	}
	
	$order_id = join (',', $ids);

	$locks = $lock->exists ('Block', $ids);
	$query = Block::query ()->where ('id in(' . join (', ', $qs) . ')');
	$query->query_params = $ids;
	$blocks = $query->fetch_orig ();
} else {
	$idsearch = str_replace ('*', '%', $data['wildcard']);
	$blocks = Block::query ()->where ('id like ?', $idsearch)
		->order ('id', 'asc')
		->fetch_orig ();

	$order_id = $data['wildcard'];
	
	$ids = array_column ($blocks, 'id');
	$locks = $lock->exists ('Block', $ids);
}

// Apply custom sorting order, if set
$order = blocks\GroupOrder::for ($order_id);
if ($order != false) {
	$ids = blocks\GroupOrder::apply_order ($ids, $order);
}

$list = array ();
foreach ($blocks as $block) {
	$list[$block->id] = $block;
}

$total = count ($blocks);
if ($units === 'auto' || (! $wildcard && $total !== count ($units))) {
	if ($total === 2) {
		$units = array (66, 33);
	} elseif ($total > 0) {
		$units = array ();
		$w = floor (100 / $total);
		for ($k = 0; $k < $total; $k++) {
			$units[$k] = $w;
		}
	}
} elseif ($wildcard && count ($units) === 1) {
	$unit = $units[0];
	for ($k = 0; $k < $total; $k++) {
		$units[$k] = $unit;
	}
}

$next_wildcard = 1;

if ($rows) echo '<div class="block-group-wrapper">' . PHP_EOL;

foreach ($ids as $k => $id) {
	if (! isset ($list[$id])) {
		if ($rows) {
			printf ('<div class="block-outer block-outer-missing" id="block-outer-%s">%s', $id, PHP_EOL);
			printf ('<div class="e-row">%s', PHP_EOL);
			printf ('<div class="e-col-%d block block-missing" id="block-%s">%s', $units[$k], $id, PHP_EOL);
		} elseif ($divs) {
			printf ('<div class="e-col-%d block block-missing" id="block-%s">%s', $units[$k], $id, PHP_EOL);
		}

		if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
			echo $tpl->render ('blocks/editable', (object) ['id' => $id, 'locked' => false, 'sorting' => true]) . PHP_EOL;
		}

		if ($rows) {
			echo '</div>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
		} elseif ($divs) {
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

	if ($rows) {
		if ($b->background != '') {
			printf ('<div class="block-outer %s" id="block-outer-%s" data-block-id="%s" data-order-id="%s" style="background-image: url(\'%s\'); background-size: cover; background-position: 50%% 50%%">%s', $b->style, $b->id, $b->id, $order_id, $b->background, PHP_EOL);
			printf ('<div class="e-row">%s', PHP_EOL);
			printf ('<div class="e-col-%d block" id="block-%s">%s', $units[$k], $b->id, PHP_EOL);
		} else {
			printf ('<div class="block-outer %s" id="block-outer-%s" data-block-id="%s" data-order-id="%s">%s', $b->style, $b->id, $b->id, $order_id, PHP_EOL);
			printf ('<div class="e-row">%s', PHP_EOL);
			printf ('<div class="e-col-%d block" id="block-%s">%s', $units[$k], $b->id, PHP_EOL);
		}
	} elseif ($divs) {
		if ($b->background != '') {
			printf ('<div class="e-col-%d block" id="block-%s" style="background-image: url(\'%s\'); background-size: cover; background-position: 50%% 50%%">%s', $units[$k], $b->id, $b->background, PHP_EOL);
		} else {
			printf ('<div class="e-col-%d block" id="block-%s">%s', $units[$k], $b->id, PHP_EOL);
		}
	}

	if ($b->show_title == 'yes') {
		printf ('<' . $level . '>%s</' . $level . '>' . PHP_EOL, $b->title);
	}

	$b->locked = is_array ($locks) ? in_array ($id, $locks) : false;

	if (User::require_acl ('admin', 'admin/edit', 'blocks')) {
		if ($rows) $b->sorting = true;
		echo $tpl->render ('blocks/editable', $b) . PHP_EOL;
	}

	echo $tpl->run_includes ($b->body) . PHP_EOL;
	
	if ($rows) {
		echo '</div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
	} elseif ($divs) {
		echo '</div>' . PHP_EOL;
	}
	
	// Determine next ID for wildcard add link
	if ($wildcard) {
		$prefix = str_replace ('*', '', $data['wildcard']);
		$block_id = str_replace ($prefix, '', $b->id);
		if (is_numeric ($block_id)) {
			$block_id = intval ($block_id);
			if ($block_id >= $next_wildcard) {
				$next_wildcard = $block_id + 1;
			}
		}
	}
}

// Add wildcard add block link
if ($wildcard && $rows && User::require_acl ('admin', 'blocks', 'admin/add')) {
	$next_id = str_replace ('*', $next_wildcard, $data['wildcard']);
	echo $tpl->render ('blocks/editable', (object) ['id' => $next_id, 'locked' => false]) . PHP_EOL;
}

// Add group order script
if ($rows) {
	echo '</div>' . PHP_EOL; // End block-group-wrapper

	if (User::require_acl ('admin', 'blocks')) {
		$page->add_style ('/apps/blocks/css/grouporder.css');
		$page->add_script ('/apps/blocks/js/grouporder.js', 'tail');
	}
}

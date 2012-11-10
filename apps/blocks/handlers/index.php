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
 * or this:
 *
 *     {! blocks/index?id=banner-[id] !}
 *
 * In the above example, `[id]` is replaced with the current page ID, so
 * that on each page, it will try to render a block in that position with
 * the same ID as the current page.
 *
 * Additionally you can also provide a fallback block in which will be 
 * displayd if there is no individual block for this page defiened
 *
 *     {! blocks/index?id=banner-[id]&fallback=banner !}
 *
 * See the API documentation for the Template class for more info on
 * `[expr]` style sub-expressions.
 */

$id = (isset ($this->params[0])) ? $this->params[0] : (isset ($data['id']) ? $data['id'] : false);
if (isset ($data['id'])) {
 	$fallback_id = (isset ($data['fallback'])) ? $data['fallback'] : false;
} 
if (! $id) {
	if (User::is_valid () && User::is ('admin')) {
		echo $tpl->render ('blocks/editable', (object) array ('id' => $id, 'locked' => false));
	}
	return;
}

$lock = new Lock ('Block', $id);

$b = new Block ($id);
if ($b->error) {	
	if ($fallback_id) {		
		$lock->remove ();
		$lock = new Lock ('Block', $fallback_id);
		$b = new Block ($fallback_id);
		$b->new_id = $id;
	}	
	if ($b->error) {
		if (User::is_valid () && User::is ('admin')) {
			echo $tpl->render ('blocks/editable', (object) array ('id' => $fallback_id, 'locked' => false, 'title' => false));
		}
		return;
	}
}

// permissions
if ($b->access !== 'public') {
	if (! User::require_login ()) {
		return;
	}
	if (! User::access ($b->access)) {
		return;
	}
}

if ($b->show_title == 'yes') {
	printf ('<h3>%s</h3>', $b->title);
}

$b->locked = $lock->exists ();

if (User::is_valid () && User::is ('admin')) {
	echo $tpl->render ('blocks/editable', $b);
}

echo $tpl->run_includes ($b->body);

?>
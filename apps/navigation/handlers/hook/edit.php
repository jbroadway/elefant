<?php

if (! $this->internal) {
	die ('Must be called by another handler');
}

$n = new Navigation;
$node = $n->node ($this->data['page']);
if ($node) {
	$node->data = (! empty ($this->data['menu_title'])) ? $this->data['menu_title'] : $this->data['title'];
	$n->save ();

	require_once ('apps/navigation/lib/Functions.php');
	navigation_clear_cache ();
}

?>
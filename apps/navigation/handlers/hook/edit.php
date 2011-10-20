<?php

if (! $this->internal) {
	die ('Must be called by another handler');
}

$n = new Navigation;
$node = $n->node ($this->data['page']);
if ($node) {
	$node->data = (! empty ($this->data['menu_title'])) ? $this->data['menu_title'] : $this->data['title'];
	if ($this->data['page'] != $this->data['id']) {
		// Update ID if renamed
		$node->attr->id = $this->data['id'];
	}
	$n->save ();
}

?>
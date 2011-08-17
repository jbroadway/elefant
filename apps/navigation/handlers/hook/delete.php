<?php

if (! $this->internal) {
	die ('Must be called by another handler');
}

$n = new Navigation;
$n->remove ($this->data['page'], false);
$n->save ();

?>
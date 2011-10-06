<?php

if (! $this->internal) {
	die ('Must be called by another handler');
}

$n = new Navigation;
$n->remove ($this->data['page'], false);
$n->save ();

require_once ('apps/navigation/lib/Functions.php');
navigation_clear_cache ();

?>
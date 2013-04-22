<?php

/**
 * Provides save function to edit screen.
 */

$this->require_acl ('admin', 'translator');
$this->restful (new Translator ());

?>
<?php

$this->require_admin ();

$page->layout = 'admin';

$page->title = i18n_get ('Translator');

echo $tpl->render ('translator/index');

?>
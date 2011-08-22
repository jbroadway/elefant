<?php

if ($this->data['code'] == 404 && @file_exists ('install') && ! @file_exists ('install/installed')) {
	$this->redirect ('/install');
}

header ('HTTP/1.1 ' . $this->data['code'] . ' ' . $this->data['title']);

$page->title = $this->data['title'];

if (! empty ($this->data['message'])) {
	echo $this->data['message'];
}

$page->template = 'admin/base';
$page->layout = 'error';

?>
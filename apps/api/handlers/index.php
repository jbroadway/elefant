<?php

$page->template = false;

header ('Location: /api/' . $appconf['Api']['current_version']);
exit;

?>
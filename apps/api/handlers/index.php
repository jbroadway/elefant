<?php

$page->layout = false;

header ('Location: /api/' . $appconf['Api']['current_version']);
exit;

?>
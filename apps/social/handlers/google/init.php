<?php

if ($controller->called['social/google/init'] > 1) {
	return;
}

echo $tpl->render ('social/google/init');

?>
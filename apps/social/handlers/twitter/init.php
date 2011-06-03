<?php

if ($controller->called['social/twitter/init'] > 1) {
	return;
}

echo $tpl->render ('social/twitter/init');

?>
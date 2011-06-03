<?php

if ($controller->called['social/facebook/init'] > 1) {
	return;
}

echo $tpl->render ('social/facebook/init');

?>
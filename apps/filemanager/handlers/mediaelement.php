<?php

if ($controller->called['filemanager/mediaelement'] > 1) {
	return;
}

echo $tpl->render ('filemanager/mediaelement');

?>
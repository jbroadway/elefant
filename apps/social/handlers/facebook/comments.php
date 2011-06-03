<?php

if (! isset ($controller->called['social/facebook/init'])) {
	echo $controller->run ('social/facebook/init');
}

echo $tpl->render ('social/facebook/comments', $data);

?>
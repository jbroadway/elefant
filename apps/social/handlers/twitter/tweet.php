<?php

if (! isset ($controller->called['social/twitter/init'])) {
	echo $controller->run ('social/twitter/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/twitter/tweet', $data);

?>
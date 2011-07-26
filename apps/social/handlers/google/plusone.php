<?php

if (! isset ($controller->called['social/google/init'])) {
	echo $controller->run ('social/google/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/google/plusone', $data);

?>
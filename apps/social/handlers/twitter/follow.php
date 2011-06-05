<?php

if (! isset ($controller->called['social/twitter/init'])) {
	echo $controller->run ('social/twitter/init');
}

if (! isset ($data['twitter_id'])) {
	$data['twitter_id'] = $appconf['Twitter']['id'];
}

echo $tpl->render ('social/twitter/follow', $data);

?>
<?php

if (! isset ($controller->called['social/twitter/init'])) {
	echo $controller->run ('social/twitter/init');
}

if (! isset ($data['twitter_id'])) {
	$appconf = parse_ini_file ('apps/social/conf/config.php');
	$data['twitter_id'] = $appconf['twitter_id'];
}

echo $tpl->render ('social/twitter/follow', $data);

?>
<?php

$query = parse_url ($data['url'], PHP_URL_QUERY);
parse_str ($query, $params);
if (isset ($params['v'])) {
	$data['video'] = $params['v'];
} else {
	$data['video'] = substr (parse_url ($data['url'], PHP_URL_PATH), 1);
}

echo $tpl->render ('social/video/youtube', $data);

?>
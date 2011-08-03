<?php

$query = parse_url ($data['url'], PHP_URL_QUERY);
parse_str ($query, $params);
$data['video'] = $params['v'];

echo $tpl->render ('social/google/youtube', $data);

?>
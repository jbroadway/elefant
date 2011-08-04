<?php

$data['video'] = substr (parse_url ($data['url'], PHP_URL_PATH), 1);

echo $tpl->render ('social/video/vimeo', $data);

?>
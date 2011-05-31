<?php

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

echo $tpl->render ('blog/post', $p->orig ());

?>
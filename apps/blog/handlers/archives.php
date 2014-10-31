<?php

$this->cache = 3600;

$res = blog\Post::archive_months ();

$months = explode (
	' ',
	__ ('January February March April May June July August September October November December')
);

echo $tpl->render (
	'blog/archives',
	array (
		'months' => $months,
		'archives' => $res
	)
);

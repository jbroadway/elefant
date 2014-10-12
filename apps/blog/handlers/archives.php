<?php

$this->cache = 3600;

$res = DB::fetch (
	'select year(ts) as year, month(ts) as month, count(*) as total
	 from #prefix#blog_post
	 where published = "yes"
	 group by year, month
	 order by year desc, month desc'
);

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

?>
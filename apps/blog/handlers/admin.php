<?php

/**
 * Admin page where you can edit posts and create new ones.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blog');

require_once ('apps/blog/lib/Filters.php');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;
$q = isset ($_GET['q']) ? $_GET['q'] : ''; // search query
$q_fields = array ('title', 'author', 'tags', 'body');
$q_exact = array ('author', 'published');
$m = isset ($_GET['m']) ? $_GET['m'] : ''; // month query
$url = '/blog/admin?q=' . urlencode ($q) . '&m=' . urlencode ($m) . '&offset=%d';

$lock = new Lock ();

// adds yyyy-mm range to query, called via closure
function blog_admin_where_month ($q, $m) {
	if (preg_match ('/^[0-9]{4}-[0-9]{2}$/', $m)) {
		$start = $m . '-01 00:00:00';
		$end = $m . '-' . gmdate ('t', strtotime ($start)) . ' 23:59:59';
		$q->where ('ts >= ?', $start);
		$q->and_where ('ts <= ?', $end);
	} else {
		$q->where ('1 = 1');
	}
}

$posts = blog\Post::query ('id, title, ts, author, published, tags')
	->where_search ($q, $q_fields, $q_exact)
	->and_where (function ($q) use ($m) {
		blog_admin_where_month ($q, $m);
	})
	->order ('ts desc')
	->fetch_orig ($limit, $offset);

$count = blog\Post::query ()
	->where_search ($q, $q_fields, $q_exact)
	->and_where (function ($q) use ($m) {
		blog_admin_where_month ($q, $m);
	})
	->count ();

foreach ($posts as $k => $p) {
	$posts[$k]->locked = $lock->exists ('Blog', $p->id);
	$posts[$k]->tags = preg_split ('/, ?/', $posts[$k]->tags);
}

$page->title = __ ('Blog Posts');
echo $tpl->render ('blog/admin', array (
	'limit' => $limit,
	'total' => $count,
	'posts' => $posts,
	'count' => count ($posts),
	'url' => $url,
	'q' => $q,
	'm' => $m,
	'archives' => blog\Post::archive_months (false),
	'months' => explode (' ', __ ('Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec'))
));

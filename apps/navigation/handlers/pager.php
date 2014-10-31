<?php

/**
 * Generates a reusable pager for lists of data.
 *
 * Usage:
 *
 * 1. Set your data as follows:
 *
 *     $limit = 20;
 *     $num = $this->params[0]; // from the URL, e.g. /myapp/handler/#
 *     $offset = $num * $limit;
 *
 *     $items = MyModel::query ()->fetch ($limit, $offset);
 *
 *     $data = array (
 *         'limit' => $limit,
 *         'total' => MyModel::query ()->count (),
 *         'items' => $items,
 *         'count' => count ($items),
 *         'url' => '/myapp/handler/%d'
 *     );
 *
 *     echo $tpl->render ('myapp/view', $data);
 *
 * 2. In your template
 *
 *     {! navigation/pager?style=text&url=[url]&total=[total]&count=[count]&limit=[limit] !}
 *
 * Will show:
 *
 *     << Newer results              Older results >>
 *
 *     {! navigation/pager?style=numbers&url=[url]&total=[total]&count=[count]&limit=[limit] !}
 *
 * Will show:
 *
 *     << 1 2 3 4 >>
 *
 *     {! navigation/pager?style=results&url=[url]&total=[total]&count=[count]&limit=[limit] !}
 *
 * Will show:
 *
 *     1 to 20 of 32 results:
 *
 * All elements can be styled with CSS classes.
 */

$o = new StdClass;

// the pager template to display
$styles = array ('text', 'numbers', 'results');
if (! isset ($data['style']) || ! in_array ($data['style'], $styles)) {
	$data['style'] = 'text';
}

$o->limit = $data['limit']; // number of results per set
$o->total = $data['total']; // total number of results
$o->count = $data['count']; // count of results in this set
$o->url = str_replace ('&amp;', '&', $data['url']); // the url format for building pager links

// the page number from the current url, or zero
$url = str_replace ('%d', '([0-9]+)', preg_quote ($o->url));
$o->num = (preg_match ('|' . $url . '|', $_SERVER['REQUEST_URI'], $matches))
	? (int) $matches[1]
	: 1;

$o->offset = ($o->num - 1) * $o->limit; // item offset
$o->last = $o->offset + $o->count; // the last item on this screen
$o->more = ($o->total > $o->last) ? true : false; // is there more
$o->next = $o->num + 1; // the num for the next screen
$o->prev = $o->num - 1; // the num for the previous screen
$o->last_screen = ceil ($o->total / $o->limit); // the num of the last screen

$o->url = preg_replace ('/%([^d])/', '%%\1', $o->url);
$o->next_link = sprintf ($o->url, $o->next);
$o->prev_link = sprintf ($o->url, $o->prev);
$o->first_link = sprintf ($o->url, 1);
$o->last_link = sprintf ($o->url, $o->last_screen);

$o->links = array ();
$start = ($o->num - 3 > 0) ? $o->num - 3 : 1;
$end = ($o->num + 3 <= $o->last_screen) ? $o->num + 3 : $o->last_screen;
for ($i = $start; $i <= $end; $i++) {
	$o->links[$i] = sprintf ($o->url, $i);
}

if (isset ($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) $o->url .= '?'.$_SERVER["QUERY_STRING"];

if ($data['style'] === 'results') {
	if ($o->total == 0) {
		echo __ ('No results.');
	} elseif ($o->total == 1) {
		echo __ ('1 result:');
	} else {
		echo __ (
			'%d to %d of %d results:',
			($o->offset + 1),
			$o->last,
			$o->total
		);
	}
	return;
}

echo $tpl->render ('navigation/pager/' . $data['style'], $o);

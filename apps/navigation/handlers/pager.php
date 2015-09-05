<?php

/**
 * Generates a reusable pager for lists of data.
 *
 * Usage:
 *
 * 1. Set your data as follows:
 *
 *     $limit = 20;
 *     $num = isset ($this->params[0])
 *         ? $this->params[0] // from the URL, e.g. /myapp/handler/#
 *         : 1;
 *     $offset = ($num - 1) * $limit;
 *
 *     $items = MyModel::query ()->fetch ($limit, $offset);
 *     $total = MyModel::query ()->count ();
 *
 *     $data = array (
 *         'limit' => $limit,
 *         'total' => $total,
 *         'items' => $items,
 *         'count' => count ($items),
 *         'single' => __ ('page'),
 *         'plural' => __ ('pages'),
 *         'url' => '/myapp/handler/%d'
 *     );
 *
 *     echo $tpl->render ('myapp/view', $data);
 *
 * 2. In your template
 *
 * Text pager:
 *
 *     {! navigation/pager
 *         ?style=text
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit] !}
 *
 * Will show:
 *
 *     << Newer results              Older results >>
 *
 * -----
 *
 * Text pager with a custom label:
 *
 *     {! navigation/pager
 *         ?style=text
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit]
 *         &label=pages !}
 *
 * Will show:
 *
 *     << Newer pages              Older pages >>
 *
 * -----
 * 
 * Numeric pager:
 *
 *     {! navigation/pager
 *         ?style=numbers
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit] !}
 *
 * Will show:
 *
 *     << 1 2 3 4 >>
 *
 * -----
 * 
 * Numeric pager with extra links:
 *
 *     {! navigation/pager
 *         ?style=numbers
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit]
 *         &extra[all]=All pages
 *         &extra[/search]=Search !}
 *
 * Will show:
 *
 *     << 1 2 3 4 >> All pages Search
 *
 * 'All pages' will link to '/myapp/handler/all' based on the 'url' value
 * in the PHP code above, while 'Search' will link to '/search', an
 * external link.
 *
 * -----
 *
 * Standard "X to Y of Z results" pager with a custom label:
 *
 *     {! navigation/pager
 *         ?style=results
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit]
 *         &single=page
 *         &plural=pages !}
 *
 * Will show:
 *
 *     1 to 20 of 32 pages:
 *
 * -----
 * 
 * Short-form pager:
 *
 *     {! navigation/pager
 *         ?style=short
 *         &url=[url]
 *         &total=[total]
 *         &count=[count]
 *         &limit=[limit] !}
 *
 * Will show:
 *
 *     1-20 of 32:
 *
 * All elements can be styled with CSS classes.
 */

$o = new StdClass;

// the pager template to display
$styles = array ('text', 'numbers', 'results', 'short');
if (! isset ($data['style']) || ! in_array ($data['style'], $styles)) {
	$data['style'] = 'text';
}

$o->limit = $data['limit']; // number of results per set
$o->total = $data['total']; // total number of results
$o->count = $data['count']; // count of results in this set
$o->url = str_replace ('&amp;', '&', $data['url']); // the url format for building pager links
$o->single = isset ($data['single']) ? $data['single'] : __ ('result');
$o->plural = isset ($data['plural']) ? $data['plural'] : __ ('results');
$o->extra = (isset ($data['extra']) && is_array ($data['extra'])) ? $data['extra'] : array ();

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

foreach ($o->extra as $val => $label) {
	if (strpos ($val, '/') !== false) {
		$o->extra[$val] = array (
			'label' => $label,
			'url' => $val,
			'key' => $val
		);
	} else {
		$label_url = str_replace ('%d', '%s', $o->url);
		$_url = sprintf ($label_url, $val);
		$o->extra[$val] = array (
			'label' => $label,
			'url' => $_url,
			'key' => $val
		);
	}
}

$o->is_extra = isset ($o->extra[$_SERVER['REQUEST_URI']]);

$o->links = array ();
$start = ($o->num - 3 > 0) ? $o->num - 3 : 1;
$end = ($o->num + 3 <= $o->last_screen) ? $o->num + 3 : $o->last_screen;
for ($i = $start; $i <= $end; $i++) {
	$o->links[$i] = sprintf ($o->url, $i);
}

if (isset ($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) $o->url .= '?'.$_SERVER["QUERY_STRING"];

if ($data['style'] === 'results') {
	echo '<div class="pager">';
	if ($o->total == 0) {
		echo __ ('No %s.', $o->plural);
	} elseif ($o->total == 1) {
		echo __ ('1 %s:', $o->single);
	} else {
		echo __ (
			'%d to %d of %d %s:',
			($o->offset + 1),
			$o->last,
			$o->total,
			$o->label
		);
	}
	echo '</div>';
	return;
} elseif ($data['style'] === 'short') {
	echo '<div class="pager">';
	if ($o->total == 0) {
		echo __ ('No %s.', $o->plural);
	} elseif ($o->total == 1) {
		echo __ ('1 %s:', $o->single);
	} else {
		echo __ (
			'%d-%d of %d:',
			($o->offset + 1),
			$o->last,
			$o->total
		);
	}
	echo '</div>';
	return;
}

echo $tpl->render ('navigation/pager/' . $data['style'], $o);

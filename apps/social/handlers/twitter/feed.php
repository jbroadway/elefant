<?php

/**
 * Embeds a twitter feed widget into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset ($data['twitter_id'])) {
	$id = Appconf::user ('Twitter', 'twitter_id');
	$data['twitter_id'] = (! empty ($id)) ? $id : $appconf['Twitter']['id'];
}

$data['num_of_tweets'] = isset ($data['num_of_tweets']) ? $data['num_of_tweets'] : 5;
$data['show_dates'] = isset ($data['show_dates']) ? $data['show_dates'] : 'no';

$cache_key = 'social:twitter:' . $data['twitter_id'] . ':' . $data['num_of_tweets'];
$res = $cache->get ($cache_key);
if ($res) {
	return $res;
}

$twauth = new tmhOAuth (array (
	'consumer_key' => Appconf::user ('Twitter', 'consumer_key'),
	'consumer_secret' => Appconf::user ('Twitter', 'consumer_secret'),
	'user_token' => Appconf::user ('Twitter', 'access_token'),
	'user_secret' => Appconf::user ('Twitter', 'access_token_secret')
));

$code = $twauth->request (
	'GET',
	'https://api.twitter.com/1.1/statuses/user_timeline.json',
	array (
		'screen_name' => $data['twitter_id'],
		'count' => $data['num_of_tweets']
	)
);

$res = json_decode ($twauth->response['response']);

if ($code !== 200) {
	error_log (sprintf (
		'Error requesting tweets: [%d] %s',
		$res->errors[0]->code,
		$res->errors[0]->message
	));
}

$data['tweets'] = $res;

$out = $tpl->render ('social/twitter/feed', $data);
$cache->set ($cache_key, $out, 0, 1800);
echo $out;

?>

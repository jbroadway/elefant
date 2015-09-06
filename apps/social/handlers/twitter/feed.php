<?php

/**
 * Embeds a Twitter feed widget into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/twitter/feed', array ('twitter_id' => 'twitter_handle'));
 *
 * In a template, call it like this:
 *
 *     {! social/twitter/feed?twitter_id=twitter_handle !}
 *
 * Parameters:
 *
 * - `twitter_id` - The Twitter ID to show a feed of (default = Twitter ID setting).
 * - `num_of_tweets` - Number of tweets to show (default = 5)
 * - `show_dates` - Whether to show the tweet dates (default = no)
 *
 * Also available in the dynamic objects menu as "Twitter: Feed".
 *
 * > Note: Requires you to register a Twitter app, then enter your
 * > Twitter app credentials on the Accounts > Settings screen.
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

<?php

/**
 * Helper for displaying the cookie usage notice required by the EU.
 * All parameters are optional.
 *
 * Based on https://cookieinfoscript.com/
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run (
 *         'social/cookienotice',
 *         [
 *             'bg' => 'f5f5f5'
 *             'link' => 'f90'
 *             'msg' => 'C is for cookie'
 *         ]
 *     );
 *
 * In a template, call it like this:
 *
 *     {! social/cookienotice
 *         ?bg=f5f5f5
 *         &link=f90
 *         &msg=C is for cookie !}
 *
 * Parameters:
 *
 * - `bg` - Background colour code (default: eee).
 * - `fg` - Banner text colour code (default: 333).
 * - `link` - Link text colour (default: 31a8f0).
 * - `linkbg` - Link background colour (default: f1d600).
 * - `fontsize` - Font size (default: 14px).
 * - `height` - Banner height (default: 31px).
 * - `zindex` - Z-index of the banner (default: 255).
 * - `align` - Text alignment (default: center).
 * - `position` - Position (top or bottom, default: bottom).
 * - `msg` - The message to display.
 * - `moreinfo` - A link to your cookie policy (optional).
 * - `cookie` - Cookie name (default: we-love-cookies).
 */

$data['msg'] = isset ($data['msg'])
	? $data['msg']
	: __ ('We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.');

$data['bg'] = isset ($data['bg']) ? $data['bg'] : 'eee';
$data['fg'] = isset ($data['fg']) ? $data['fg'] : '333';
$data['link'] = isset ($data['link']) ? $data['link'] : '31a8f0';
$data['linkbg'] = isset ($data['linkbg']) ? $data['linkbg'] : 'f1d600';
$data['fontsize'] = isset ($data['fontsize']) ? $data['fontsize'] : '14px';
$data['height'] = isset ($data['height']) ? $data['height'] : '31px';
$data['zindex'] = isset ($data['zindex']) ? $data['zindex'] : '255';
$data['position'] = isset ($data['position']) ? $data['position'] : 'bottom';
$data['align'] = isset ($data['align']) ? $data['align'] : 'center';
$data['moreinfo'] = isset ($data['moreinfo']) ? $data['moreinfo'] : 'https://wikipedia.org/wiki/HTTP_cookie';
$data['cookie'] = isset ($data['cookie']) ? $data['cookie'] : 'we-love-cookies';

echo $tpl->render ('social/cookienotice', $data);

info ($data, true);

<?php

/**
 * Embeds a Google map into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run (
 *         'social/google/maps',
 *         array (
 *             'address' => '123 Broadway',
 *             'city' => 'New York',
 *             'state' => 'NY',
 *             'country' => 'USA',
 *             'zip' => 10203
 *         )
 *     );
 *
 * In a template, call it like this:
 *
 *     {! social/google/maps?
 *         ?address=123 Broadway
 *         &city=New York
 *         &state=NY
 *         &country=USA
 *         &zip=10203 !}
 *
 * Parameters:
 *
 * - `address` - Street address
 * - `city` - City
 * - `state` - State/province
 * - `country` - Country
 * - `zip` - Zip/postal code
 * - `width` = Width of map (default=100%)
 * - `height` = Height of map (default=400px)
 *
 * Also available in the dynamic objects menu as "Google: Map".
 */

$data['map_id'] = rand ();

$data['width'] = (isset ($data['width']) && ! empty ($data['width'])) ? $data['width'] : '100%';
$data['height'] = (isset ($data['height']) && ! empty ($data['height'])) ? $data['height'] : '400px';
$data['width'] = is_numeric ($data['width']) ? $data['width'] . 'px' : $data['width'];
$data['height'] = is_numeric ($data['height']) ? $data['height'] . 'px' : $data['height'];

$page->add_script ($tpl->render ('social/google/maps_loader', $data));
$page->add_script ($tpl->render ('social/google/maps_script', $data));

echo $tpl->render ('social/google/maps', $data);

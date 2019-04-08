<?php

/**
 * Creates a before & after image slider between two images.
 */

if (! $this->internal) return;

$data['id'] = uniqid();

$page->add_style ('https://unpkg.com/beerslider/dist/BeerSlider.css');
$page->add_script ('https://unpkg.com/beerslider/dist/BeerSlider.js', 'tail');
$page->add_script ('<script>new BeerSlider(document.getElementById("before-after-' . $data['id'] . '"))</script>', 'tail');

echo $tpl->render ('filemanager/util/before-after', $data);

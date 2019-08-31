<?php

/**
 * Creates a before & after image slider between two images.
 */

if (! $this->internal) return;

$data['id'] = uniqid ();

$page->add_style ('https://unpkg.com/beerslider@1.0.3/dist/BeerSlider.css', 'head', '', 'sha384-ZveTPUF1SurDs6nJkjOW+d0sLlPO23ctWsQWj4w1qdzVXTurxziP922rCiqd4jrf', 'anonymous');
$page->add_script ('https://unpkg.com/beerslider@1.0.3/dist/BeerSlider.js', 'tail', '', 'sha384-IqsSf+qPrMYFru/xpdalu+CdlKy4UGVzOAvWDC6dYQOKBLtTfrxaIXxyehlCqerc', 'anonymous');
$page->add_script ('<script>new BeerSlider(document.getElementById("before-after-' . $data['id'] . '"))</script>', 'tail');

echo $tpl->render ('filemanager/util/before-after', $data);

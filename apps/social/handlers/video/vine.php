<?php

/**
 * Embeds a Vine.co video into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */
$data['audio'] = ($data['audio'] == 'yes')?0:1;
$page->add_script("https://platform.vine.co/static/scripts/embed.js");
echo $tpl->render ('social/video/vine', $data);

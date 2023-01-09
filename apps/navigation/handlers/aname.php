<?php

/**
 * Embeds a customizable named anchor into a page so site editors can link
 * to it by linking to `#my-named-anchor`.
 */

if (! isset ($data['linkname'])) return;

echo '<a name="' . Template::quotes (trim ($data['linkname'], " \n\r\t\v\x00#")) . '"></a>';

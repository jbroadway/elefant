<?php

/**
 * Adds emoji support via the emojiOne library for entering emojis
 * into form fields.
 *
 * Usage:
 *
 * ### 1. Load this handler either in your handler
 *
 *     $this->run ('admin/util/emoji');
 *
 * Or anywhere in your view:
 *
 *      {! admin/util/emoji !}
 *
 * ### 2. Use the emojiOne library:
 *
 *     $('#title').emojioneArea ({
 *         unicodeAlt: true
 *     });
 *
 * For more usage info, visit:
 *
 * https://github.com/emojione/emojione
 */

$page->add_style ('//cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.css');
$page->add_script ('//cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.js');

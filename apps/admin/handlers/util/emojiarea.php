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

$page->add_style ('https://cdn.jsdelivr.net/npm/emojionearea@3.4.1/dist/emojionearea.min.css', 'head', '', 'sha256-LKawN9UgfpZuYSE2HiCxxDxDgLOVDx2R4ogilBI52oc=', 'anonymous');
$page->add_script ('https://cdn.jsdelivr.net/npm/emojionearea@3.4.1/dist/emojionearea.min.js', 'head', '', 'sha256-hhA2Nn0YvhtGlCZrrRo88Exx/6H8h2sd4ITCXwqZOdo=', 'anonymous');

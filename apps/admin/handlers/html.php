<?php

/**
 * Reverse lookup of HTML code from ID values.
 * Used in dynamic embeds so you can paste arbitrary
 * HTML into a page without alteration by the
 * WYSIWYG editor.
 */

require_once ('apps/admin/lib/Functions.php');

echo admin_embed_lookup ($data['id']);

?>
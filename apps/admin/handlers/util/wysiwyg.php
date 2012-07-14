<?php

/**
 * Call this to turn a textarea into a wysiwyg editor.
 * To specify a custom field ID, call it like this:
 *
 *     {! admin/util/wysiwyg?field_id=my_field !}
 */

echo $tpl->render ('admin/wysiwyg', $data);

?>
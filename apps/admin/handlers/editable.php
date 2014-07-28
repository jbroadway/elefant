<?php

/**
 * Use this handler to position the page edit buttons in
 * a specific place in your templates, instead of simply
 * at the top of the page body. Also useful in case your
 * template omits the `{{body|none}}` tag altegether, but
 * you still want to make the page editable for its other
 * properties.
 *
 * Usage:
 *
 *     {! admin/editable?id=[id] !}
 */

echo $tpl->render ('admin/editable', $this->data);

?>
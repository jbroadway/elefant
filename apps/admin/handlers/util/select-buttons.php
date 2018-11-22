<?php

/**
 * Converts HTML select elements into button groups.
 *
 * In PHP, load it like this:
 *
 *     $this->run ('admin/util/select-buttons');
 *
 * Or in your view template, load it like this:
 *
 *     {! admin/util/select-buttons !}
 *
 * Now you can turn any select box into buttons like this:
 *
 *     <select name="some-options" class="buttons">
 *         <option value="one">One</option>
 *         <option value="two" selected>Two</option>
 *     </select>
 *
 *     <script> $(function () { $('select.buttons').select_buttons (); }); </script>
 */

$page->add_style ('/apps/admin/css/jquery.select-buttons.css?v=1');
$page->add_script ('/apps/admin/js/jquery.select-buttons.js?v=1');

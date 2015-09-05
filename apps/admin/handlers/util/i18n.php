<?php

/**
 * Includes `$.i18n()` which makes Elefant's [[I18n]] translation support
 * available in JavaScript code.
 *
 * Usage:
 *
 * ### 1. In your view template:
 *
 *     {! admin/util/i18n !}
 *     <script>
 *     $(function () {
 *         $.i18n_append ({
 *             'Original text': "{'Translated text'}"
 *         });
 *     });
 *
 * ### 2. Now in your included JavaScript, you can use:
 *
 *     console.log ($.i18n ('Original text'));
 */

$page->add_script ('/apps/admin/js/jquery.i18n.js');

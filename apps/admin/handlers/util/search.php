<?php

/**
 * For use in conjunction with `[[Model]]::where_search()` and
 * adding terms and exact matches to a search box on an
 * admin screen.
 *
 * Usage:
 *
 *     {! admin/util/search !}
 *
 *     <script>
 *     $(function () {
 *         $.search_init ({
 *             form: '#search-form',     // form selector
 *             query: '#search-query',   // query field selector
 *             links: '.search-for',     // selector to modify search via links
 *             options: '.search-option' // selector to modify search via select boxes
 *         });
 *     });
 *     </script>
 *
 *     <form method="get" id="search-form">
 *         <input type="text" name="q" id="search-query" />
 *         <button>{"Search"}</button>
 *     </form>
 *
 *     <a href="#" class="search-for" data-search="Foo">Foo</a>
 *
 *     <a href="#"
 *        class="search-for"
 *        data-search="company:&quot;Example Inc.&quot;">Example Inc.</a>
 *
 *     <select class="search-option" data-prefix="field">
 *         ...options for field:"value" searches...
 *     </select>
 */

$page->add_script ('/apps/admin/js/jquery.search.js');

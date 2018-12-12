<?php

/**
 * A minimal responsive grid system for Elefant's apps.
 *
 * This helper includes a minimal responsive grid for laying out an app's
 * output for consistent display across websites.
 *
 * Usage:
 *
 * ### In PHP code, call it like this:
 *
 *     $this->run ('admin/util/minimal-grid');
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/minimal-grid !}
 *
 * ### Example HTML structure
 *
 *     <div class="e-row">
 *         <div class="e-col-75">
 *             <h1>Body</h1>
 *         </div>
 *         <div class="e-col-25">
 *             <h2>Sidebar</h2>
 *         </div>
 *     </div>
 *
 * ### Available classes:
 *
 *     .e-row                A centered row of 98% that scales via media queries
 *     .e-row-variable       A row of variable width
 *     .e-row-equal          A variable width row of equal height columns
 *     .e-no-padding         Removes padding on an element
 *     .e-no-padding-left    Removes padding-left on an element
 *     .e-no-padding-right   Removes padding-right on an element
 *     .e-no-padding-top     Removes padding-top on an element
 *     .e-no-padding-bottom  Removes padding-bottom on an element
 *     .e-col-5              A column of 5%
 *     .e-col-10             A column of 10%
 *     .e-col-15             A column of 15%
 *     .e-col-20             A column of 20%
 *     .e-col-25             A column of 25%
 *     .e-col-30             A column of 30%
 *     .e-col-33             A column of 33%
 *     .e-col-35             A column of 35%
 *     .e-col-40             A column of 40%
 *     .e-col-45             A column of 45%
 *     .e-col-50             A column of 50%
 *     .e-col-55             A column of 55%
 *     .e-col-60             A column of 60%
 *     .e-col-65             A column of 65%
 *     .e-col-66             A column of 66%
 *     .e-col-70             A column of 70%
 *     .e-col-75             A column of 75%
 *     .e-col-80             A column of 80%
 *     .e-col-85             A column of 85%
 *     .e-col-90             A column of 90%
 *     .e-col-95             A column of 95%
 *     .e-col-100            A column of 100%
 *     .text-right           Right-align text
 *     .text-left            Left-align text
 *     .text-justify         Justify-align text
 *
 * See the file [apps/admin/css/minimal-grid.html](/apps/admin/css/minimal-grid.html) for usage
 * examples.
 */

$page->add_script ('/apps/admin/css/minimal-grid.css?v=4');

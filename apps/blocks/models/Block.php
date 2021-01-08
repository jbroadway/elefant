<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Model for content blocks.
 *
 * Fields:
 *
 * id - Unique block ID, which is used to link blocks to page layouts.
 *      Note that some block IDs are auto-generated and shouldn't be
 *      modified or their association to layouts or pages can break.
 * title - Block title
 * body - Body content, or the first column for multi-column rows.
 * access - Whether this block is public, members-only, or private.
 * show_title - Whether to show the block title or keep it hidden.
 * background - Link to an optional background image.
 * column_layout - Column layout for blocks representing rows (see `blocks/group`)
 * col2 - Content for column 2 in a multi-column row.
 * col3 - Content for column 3 in a multi-column row.
 * col4 - Content for column 4 in a multi-column row.
 * col5 - Content for column 5 in a multi-column row.
 */
class Block extends Model {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#block';
	
	/**
	 * Link format for version history.
	 */
	public static $versions_link = '/blocks/edit?id={{id}}';

	/**
	 * Fields to display as links in version history.
	 */
	public static $versions_display_fields = [
		'title' => 'Title'
	];
	
	public static $column_layouts = [
		'100',
		'50-50',
		'60-40',
		'40-60',
		'66-33',
		'33-66',
		'70-30',
		'30-70',
		'75-25',
		'25-75',
		'80-20',
		'20-80',
		'33-33-33',
		'50-25-25',
		'25-50-25',
		'25-25-50',
		'25-25-25-25',
		'20-20-20-20-20'
	];

	/**
	 * Get a list of classes that can be applied to blocks. Looks for a `blocks.css`
	 * file in your `default_layout` folder, otherwise reads all `*.css` files found
	 * there and parses them for lines of the form `.block-outer.custom-class-name`.
	 * These will then be made available for selection in the block editor, and applied
	 * to any `blocks/group?rows=on` output.
	 */
	public static function get_styles () {
		$layout = conf ('General', 'default_layout');
		$files = file_exists ('layouts/' . $layout . '/blocks.css')
			? ['layouts/' . $layout . '/blocks.css']
			: glob ('layouts/' . $layout . '/*.css');
		$classes = [];
	
		foreach ($files as $file) {
			$css = file_get_contents ($file);
		
			if (preg_match_all ('/\.block-outer\.([a-zA-Z0-9_-]+)/', $css, $regs, PREG_SET_ORDER)) {
				foreach ($regs as $match) {
					$label = ucwords (str_replace (['-', '_'], ' ', $match[1]));
					$classes[$match[1]] = $label;
				}
			}
		}
	
		return $classes;
	}
}

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

namespace blog;

/**
 * Blog Post model.
 *
 * Fields:
 *
 * id
 * title
 * ts
 * author
 * published
 * body
 * tags
 * extra
 */
class Post extends \ExtendedModel {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#blog_post';

	/**
	 * The `extra` field can contain an arbitrary number of additional
	 * user-defined properties.
	 */
	public $_extended_field = 'extra';

	/**
	 * Get the most recently published posts.
	 */
	public static function latest ($limit = 10, $offset = 0) {
		return self::query ()->where ('published', 'yes')->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	/**
	 * Get posts by the specified author.
	 */
	public static function by ($author, $limit = 10, $offset = 0) {
		return self::query ()->where ('published', 'yes')->where ('author', $author)->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	/**
	 * Get the latest headlines only.
	 */
	public static function headlines ($limit = 10) {
		return self::query (array ('id', 'ts', 'title'))->where ('published', 'yes')->order ('ts desc')->fetch_orig ($limit);
	}

	/**
	 * Get posts by a certain tag.
	 */
	public static function tagged ($tag, $limit = 10, $offset = 0) {
		$ids = \DB::shift_array ('select post_id from #prefix#blog_post_tag where tag_id = ?', $tag);
		return self::query ()->where ('id in(' . join (',', $ids) . ')')->where ('published', 'yes')->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	/**
	 * Count posts by a certain tag.
	 */
	public static function count_by_tag ($tag, $limit = 10, $offset = 0) {
		$ids = \DB::shift_array ('select post_id from #prefix#blog_post_tag where tag_id = ?', $tag);
		return self::query ()->where ('id in(' . join (',', $ids) . ')')->where ('published', 'yes')->order ('ts desc')->count ();
	}

	/**
	 * Get a list of tags and the number of posts they've been used on.
	 */
	public static function tags () {
		return \DB::pairs ('select tag_id, count(*) as posts from #prefix#blog_post_tag group by tag_id order by tag_id asc');
	}

	/**
	 * Generate a list of pages for the sitemaps app.
	 */
	public static function sitemap () {
		$posts = self::query ()
			->where ('published', 'yes')
			->fetch_orig ();
		
		$urls = array ();
		foreach ($posts as $post) {
			$urls[] = '/blog/post/' . $post->id . '/' . \URLify::filter ($post->title);
		}
		return $urls;
	}

	/**
	 * Generate a list of posts for the search app,
	 * and add them directly via `Search::add()`.
	 */
	public static function search () {
		$posts = self::query ()
			->where ('published', 'yes')
			->fetch_orig ();
		
		foreach ($posts as $i => $post) {
			$url = 'blog/post/' . $post->id . '/' . URLify::filter ($post->title);
			if (! Search::add (
				$url,
				array (
					'title' => $post->title,
					'text' => $post->body,
					'url' => '/' . $url
				)
			)) {
				return array (false, $i);
			}
		}
		return array (true, count ($posts));
	}
}

?>
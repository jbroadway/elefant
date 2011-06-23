<?php

namespace blog;

class Post extends \Model {
	var $table = 'blog_post';

	function latest ($limit = 10, $offset = 0) {
		$p = new Post;
		return $p->query ()->where ('published', 'yes')->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	function by ($author, $limit = 10, $offset = 0) {
		$p = new Post;
		return $p->query ()->where ('published', 'yes')->where ('author', $author)->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	function headlines ($limit = 10) {
		$p = new Post;
		return $p->query ()->where ('published', 'yes')->order ('ts desc')->fetch_assoc ('id', 'title', $limit);
	}

	function tagged ($tag, $limit = 10, $offset = 0) {
		$p = new Post;
		$ids = db_shift_array ('select post_id from blog_post_tag where tag_id = ?', $tag);
		return $p->query ()->where ('id in(' . join (',', $ids) . ')')->where ('published', 'yes')->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	function count_by_tag ($tag, $limit = 10, $offset = 0) {
		$p = new Post;
		$ids = db_shift_array ('select post_id from blog_post_tag where tag_id = ?', $tag);
		return $p->query ()->where ('id in(' . join (',', $ids) . ')')->where ('published', 'yes')->order ('ts desc')->count ();
	}

	function tags () {
		return db_pairs ('select tag_id, count() as posts from blog_post_tag group by tag_id order by tag_id asc');
	}
}

?>
<?php

namespace blog;

class Post extends \Model {
	var $table = 'blog_post';

	function latest ($limit = 10, $offset = 0) {
		$p = new Post;
		return $p->query ()->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	function by ($author, $limit = 10, $offset = 0) {
		$p = new Post;
		return $p->query ()->where ('author', $author)->order ('ts desc')->fetch_orig ($limit, $offset);
	}

	function headlines ($limit = 10) {
		$p = new Post;
		return $p->query ()->order ('ts desc')->fetch_assoc ('id', 'title', $limit);
	}
}

?>
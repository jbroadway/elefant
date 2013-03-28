<?php

/**
 * Switch to display the comment count for a post.
 */

switch ($appconf['Blog']['comments']) {
	case 'disqus':
		echo $this->run ('blog/disqus/commentcount', $data);
		break;
	case 'facebook':
		printf (
			'<a href="%s">%s %s</a>',
			$data['url'],
			$this->run ('social/facebook/commentcount', $data),
			__ ('comments')
		);
		break;
}

?>
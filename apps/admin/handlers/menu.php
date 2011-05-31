<?php

$menu = Webpage::query ()
	->order ('title asc')
	->fetch_assoc ('id', 'title');

echo '<ul>';
foreach ($menu as $id => $title) {
	printf ('<li><a href="/%s">%s</a></li>', $id, $title);
}
echo '</ul>';

?>
<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('blog', $appconf['Admin']['version']) === true) {
	$page->title = i18n_get ('Upgrade completed');
	echo '<p><a href="/blog/admin">' . i18n_get ('Continue') . '</a></p>';
	return;
}

$page->title = i18n_get ('Upgrading Blog App');

$db = DB::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
switch ($dbtype) {
	case 'pgsql':
	case 'mysql':
		DB::execute ('alter table elefant_blog_post add column extra text not null default \'\'');
		break;
	case 'sqlite':
		DB::execute ('alter table elefant_blog_post add column "extra" "text not null"');
		break;
}
echo '<p>' . i18n_get ('Done.') . '</p>';

$this->mark_installed ('blog', $appconf['Admin']['version']);

?>
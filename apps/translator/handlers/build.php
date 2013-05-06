<?php

/**
 * Build or rebuild the translation index from all the source files
 * and templates on the site.
 */

$this->require_acl ('admin', 'translator');

$page->layout = 'admin';

if (! isset ($this->params[0])) {
	if (! file_exists ('lang/_index.php')) {
		$page->title = __ ('Building index');
	} else {
		$page->title = __ ('Rebuilding index');
	}

	echo 'Please wait...';
	$page->add_script ('<meta http-equiv="refresh" content="0;url=/translator/build/running" />');
	return;
}

// Build the index
$sources = array (
	'layouts/*.html',
	'layouts/*/*.html',
	'apps/*/views/*.html',
	'apps/*/views/*/*.html',
	'apps/*/views/*/*/*.html',
	'apps/*/handlers/*.php',
	'apps/*/handlers/*/*.php',
	'apps/*/handlers/*/*/*.php',
	'apps/*/lib/*.php',
	'apps/*/models/*.php',
	'apps/*/conf/acl.php',
	'install/*.php',
	'install/layouts/*.html'
);
if (file_exists ('lang/_index.php')) {
	$list = unserialize (file_get_contents ('lang/_index.php'));
} else {
	$list = array ();
}

set_time_limit (90);

foreach ($sources as $source) {
	$files = glob ($source);
	$files = is_array ($files) ? $files : array ();
	foreach ($files as $file) {
		$data = file_get_contents ($file);
		if (preg_match ('/\.html/', $file)) {
			// parse for {""} syntax
			preg_match_all ('/\{[\'"] ?(.*?) ?[\'"]\}/', $data, $matches);
			foreach ($matches[1] as $str) {
				if (! isset ($list[$str])) {
					$list[$str] = array (
						'orig' => $str,
						'src' => array ($file)
					);
				} else {
					$list[$str]['src'] = is_array ($list[$str]['src'])
						? $list[$str]['src']
						: array ($list[$str]['src']);

					if (! in_array ($file, $list[$str]['src'])) {
						$list[$str]['src'][] = $file;
					}
				}
			}
		} elseif (preg_match ('|^apps/.*/conf/acl\.php$|', $file)) {
			$strings = parse_ini_string ($data);
			foreach ($strings as $str) {
				if (! isset ($list[$str])) {
					$list[$str] = array (
						'orig' => $str,
						'str' => array ($file)
					);
				} else {
					$list[$str]['src'] = is_array ($list[$str]['src'])
						? $list[$str]['src']
						: array ($list[$str]['src']);
					
					if (! in_array ($file, $list[$str]['src'])) {
						$list[$str]['src'][] = $file;
					}
				}
			}
		} else {
			// parse for i18n_getf?() syntax
			preg_match_all ('/(i18n_getf?|__) ?\([\'"](.*?)[\'"]/', $data, $matches);
			foreach ($matches[2] as $str) {
				$str = stripslashes ($str);
				if (! isset ($list[$str])) {
					$list[$str] = array (
						'orig' => $str,
						'src' => array ($file)
					);
				} else {
					$list[$str]['src'] = is_array ($list[$str]['src'])
						? $list[$str]['src']
						: array ($list[$str]['src']);

					if (! in_array ($file, $list[$str]['src'])) {
						$list[$str]['src'][] = $file;
					}
				}
			}

			// parse for I18n::export syntax
			preg_match_all ('/I18n::export\s+?\(([^\)]+)\)/s', $data, $matches);
			foreach ($matches[1] as $match) {
				if (! preg_match ('/array\s+?\(/', $match)) {
					$match = 'array (' . $match;
				}
				$match = '<?php $__tmp__ = ' . $match . ');?>';

				$tokens = token_get_all ($match);
				foreach ($tokens as $tok) {
					if ($tok[0] === T_CONSTANT_ENCAPSED_STRING) {
						$str = stripslashes (trim ($tok[1], '"\''));
						if (! isset ($list[$str])) {
							$list[$str] = array (
								'orig' => $str,
								'src' => array ($file)
							);
						} else {
							$list[$str]['src'] = is_array ($list[$str]['src'])
								? $list[$str]['src']
								: array ($list[$str]['src']);

							if (! in_array ($file, $list[$str]['src'])) {
								$list[$str]['src'][] = $file;
							}
						}
					}
				}
			}
		}
	}
}
asort ($list);
file_put_contents ('lang/_index.php', serialize ($list));
chmod ('lang/_index.php', 0666);

$page->title = __ ('Indexing completed');

echo '<p><a href="/translator/index">' . __ ('Continue') . '</a></p>';

?>

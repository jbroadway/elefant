<?php

namespace admin;

use \Appconf;

class API extends \Restful {

	public function post_toolbar () {
		$this->controller->require_acl('admin','admin/toolbar');
		$out = array();
		if (isset($_POST['data']) && count($_POST['data'])) {
			foreach ($_POST['data'] as $section) {
				if (count($section['tools'])) {
					$out[$section['name']] = array();
					foreach ($section['tools'] as $tool) {
						$out[$section['name']][$tool['handler']] = $tool['name'];
					}
				}
			}
		}
		if (isset($_POST['autofill']) && $_POST['autofill'] > 0) {
			$out[Appconf::admin('General','autofill_column')]['*'] = '*';
		}
		return Toolbar::save($out);
	}
}

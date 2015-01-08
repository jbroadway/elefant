<?php

namespace admin;

class API extends \Restful {

	public function post_update () {
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
		return Toolbar::save($out);
	}
}

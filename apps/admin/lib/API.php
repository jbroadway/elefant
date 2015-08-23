<?php

namespace admin;

use \Appconf;
use \Ini;

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
	
	public function post_routes() {
		$this->controller->require_acl('admin','admin/routes');
		$out = array('Alias'=>array(),'Disable'=>array(),'Redirect'=>array());
		foreach($_POST as $section => $group) {
			if (isset($out[$section])) {
			foreach($group as $match => $action) {
				$out[$section][$match] = $action;
			}
			}
		}
		$path = conf_env_path('routes');
		if (Ini::write($out,$path)) return true;
		else $this->error('Unable to write to config file.');
	}
}

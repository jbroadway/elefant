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
	
	public function get_async() {
		if (!isset($_GET['page'])) return $this->error('No page route given.');
		// parse any GET values from the original URL
		$params = explode('?',$_GET['page']);
		unset($_GET['page']);
		$route = $params[0];
		if(isset($params[1])) $params = explode('&',$params[1]);
		foreach ($params as $param) {
			list($key,$value) = explode('=',$param);
			$_GET[$key] = $value;
		}
		// Set relevant URL globals that should contain the route value
		$_SERVER['REQUEST_URI'] = $_COOKIE['elefant_last_page'] = $_GET['redirect'] = ($route == '/') ? '/index' : $route;
		// This is sort of a hack to prevent any layouts from being rendered.
		\Page::$bypass_layout = true; 
		return array(
			'path'=>$route,
			'html'=>$this->controller->run ($route),
			'page'=>(array) $this->controller->page()
		);
	}
}
